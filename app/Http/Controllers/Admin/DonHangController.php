<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\KhachHang;
use App\Models\Shop;
use App\Models\LichSuTrangThai;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;
use App\Services\DeliveryFeeService;
use App\Services\PricingService;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DonHangController extends Controller
{
    /**
     * Danh sách đơn hàng — lọc trạng thái + tìm kiếm + paginate
     */
    public function index(Request $request)
    {
        $query = DonHang::with(['khachHang', 'taiXe', 'trangThai'])->latest();

        // Lọc theo trang_thai_id
        if ($request->filled('trang_thai')) {
            $query->where('trang_thai_id', $request->trang_thai);
        }

        // Tìm kiếm theo mã đơn hoặc tên khách hàng
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ma_don', 'like', "%$search%")
                  ->orWhereHas('khachHang', fn($q2) => $q2->where('ten_khach', 'like', "%$search%"));
            });
        }

        $donHangs   = $query->paginate(15)->withQueryString();
        $trangThais = TrangThaiDonHang::all();
        $taiXes     = TaiXe::where('trang_thai', '!=', 'Tam nghi')->orderBy('ho_ten')->get();

        // Thống kê count cho từng trạng thái
        $counts = ['tat_ca' => DonHang::count()];
        foreach ($trangThais as $tt) {
            $counts[$tt->id] = DonHang::where('trang_thai_id', $tt->id)->count();
        }

        return view('admin.don_hang.index', compact('donHangs', 'trangThais', 'counts', 'taiXes'));
    }

    /**
     * Chi tiết đơn hàng — load đầy đủ relationships
     */
    public function show(DonHang $donHang)
    {
        // Eager load tất cả relationships cần hiển thị
        $donHang->load([
            'khachHang',
            'taiXe',
            'trangThai',
            'lichSuTrangThais.trangThai',   // N+1 safe: load nested
            'lichSuTrangThais.nguoiThayDoi',
        ]);

        // Load danh sách trạng thái cho dropdown form cập nhật nhanh
        $trangThais = TrangThaiDonHang::all();

        $fee = app(DeliveryFeeService::class)->getBreakdown(
            (float) ($donHang->shipping_fee ?? 0),
            (float) ($donHang->cod_fee ?? 0),
        );

        return view('admin.don_hang.show', compact('donHang', 'trangThais', 'fee'));
    }

    /**
     * Form tạo đơn hàng mới
     */
    public function create()
    {
        $khachHangs = KhachHang::orderBy('ten_khach')->get();
        $shops      = Shop::orderBy('ten_shop')->get();
        $taiXes     = TaiXe::where('trang_thai', '!=', 'Tam nghi')->orderBy('ho_ten')->get();
        $trangThais = TrangThaiDonHang::all();

        return view('admin.don_hang.create', compact('khachHangs', 'shops', 'taiXes', 'trangThais'));
    }

    /**
     * Lưu đơn hàng mới + ghi lịch sử trạng thái ban đầu
     */
    public function store(Request $request)
    {
        $rules = [
            'is_new_customer'        => 'required|in:0,1',
            'sender_id'              => 'required|exists:shops,id',
            'tai_xe_id'              => 'nullable|exists:tai_xe,id',
            'weight'                 => 'nullable|integer|min:0',
            'length'                 => 'nullable|integer|min:0',
            'width'                  => 'nullable|integer|min:0',
            'height'                 => 'nullable|integer|min:0',
            'cod_amount'             => 'nullable|numeric|min:0',
            'delivery_type'          => 'nullable|string',
            'ghi_chu'                => 'nullable|string|max:500',
            'thoi_gian_giao_du_kien' => 'nullable|date',
        ];

        // Rẽ nhánh validate theo loại khách
        if ($request->is_new_customer == '1') {
            $rules['ten_khach']             = 'required|string|max:100';
            $rules['so_dien_thoai']         = 'required|string|max:20';
            $rules['newkh_tinh_id']         = 'nullable|string|exists:tinh_thanh,id';
            $rules['newkh_quan_id']         = 'nullable|string|exists:quan_huyen,id';
            $rules['newkh_xa_id']           = 'nullable|string|exists:xa_phuong,id';
            $rules['newkh_dia_chi_cu_the']  = 'nullable|string|max:255';
            $rules['latitude']              = 'nullable|numeric';
            $rules['longitude']             = 'nullable|numeric';
        } else {
            $rules['khach_hang_id'] = 'required|exists:khach_hang,id';
        }

        $validated = $request->validate($rules);
        $validated['trang_thai_id'] = $validated['trang_thai_id'] ?? TrangThaiDonHang::CHO_XU_LY;

        // Xử lý khách hàng
        $khachHangId = null;
        if ($request->is_new_customer == '1') {
            // Build địa chỉ
            $xaId        = $request->newkh_xa_id;
            $diaChiCuThe = $request->newkh_dia_chi_cu_the;
            $lat         = $request->latitude;
            $lng         = $request->longitude;
            $diaChiFull  = $diaChiCuThe;

            if ($xaId) {
                $xa = \App\Models\XaPhuong::with('quanHuyen.tinhThanh')->find($xaId);
                if ($xa) {
                    $lat = $lat ?: $xa->latitude;
                    $lng = $lng ?: $xa->longitude;
                    $parts = array_filter([$diaChiCuThe, $xa->name, $xa->quanHuyen?->name, $xa->quanHuyen?->tinhThanh?->name]);
                    $diaChiFull = implode(', ', $parts);
                }
            }

        // Tự động thêm mới Khách hàng
        $khachHang = KhachHang::create([
            'ten_khach'      => $request->ten_khach,
            'so_dien_thoai'  => $request->so_dien_thoai,
            'tinh_thanh_id'  => $request->newkh_tinh_id,
            'quan_huyen_id'  => $request->newkh_quan_id,
            'xa_phuong_id'   => $request->newkh_xa_id,
            'dia_chi_cu_the' => $diaChiCuThe,
            'latitude'       => $lat,
            'longitude'      => $lng,
        ]);
            $khachHangId = $khachHang->id;
        } else {
            $khachHangId = $request->khach_hang_id;
        }

        $validated['khach_hang_id'] = $khachHangId;

        // Tự động tính tiền bằng PricingService
        $pricing = app(PricingService::class)->calculateAll($validated);

        $validated['shipping_fee']     = $pricing['shipping_fee'];
        $validated['cod_fee']          = $pricing['cod_fee'];
        $validated['total_collection'] = ($validated['cod_amount'] ?? 0) + $pricing['shipping_fee'];

        // Lọc bớt key không thuộc DonHang
        unset($validated['is_new_customer'], $validated['ten_khach'], $validated['so_dien_thoai'],
              $validated['newkh_tinh_id'], $validated['newkh_quan_id'], $validated['newkh_xa_id'],
              $validated['newkh_dia_chi_cu_the'], $validated['latitude'], $validated['longitude']);

        // Tạo đơn hàng
        $donHang = DonHang::create(array_merge($validated, ['ma_don' => '']));
        $donHang->update(['ma_don' => DonHang::generateMaDon($donHang->id)]);

        // Ghi lịch sử trạng thái ban đầu
        LichSuTrangThai::create([
            'don_hang_id'    => $donHang->id,
            'trang_thai_id'  => $donHang->trang_thai_id,
            'nguoi_thay_doi' => Auth::id(),
            'thoi_diem'      => now(),
            'ghi_chu'        => 'Tạo đơn hàng',
        ]);

        return redirect()
            ->route('admin.don_hang.index')
            ->with('success', "Đã tạo đơn hàng {$donHang->ma_don} thành công!");
    }

    /**
     * Form chỉnh sửa đơn hàng
     */
    public function edit(DonHang $donHang)
    {
        $khachHangs = KhachHang::orderBy('ten_khach')->get();
        $shops      = Shop::orderBy('ten_shop')->get();
        $taiXes     = TaiXe::orderBy('ho_ten')->get();
        $trangThais = TrangThaiDonHang::all();

        return view('admin.don_hang.edit', compact('donHang', 'khachHangs', 'shops', 'taiXes', 'trangThais'));
    }

    /**
     * Cập nhật đơn hàng + ghi lịch sử trạng thái nếu trạng thái thay đổi
     */
    public function update(Request $request, DonHang $donHang)
    {
        $rules = [
            'is_new_customer'        => 'required|in:0,1',
            'sender_id'              => 'required|exists:shops,id',
            'tai_xe_id'              => 'nullable|exists:tai_xe,id',
            'trang_thai_id'          => 'required|exists:trang_thai_don_hang,id',
            'weight'                 => 'nullable|integer|min:0',
            'length'                 => 'nullable|integer|min:0',
            'width'                  => 'nullable|integer|min:0',
            'height'                 => 'nullable|integer|min:0',
            'cod_amount'             => 'nullable|numeric|min:0',
            'delivery_type'          => 'nullable|string',
            'ghi_chu'                => 'nullable|string|max:500',
            'thoi_gian_giao_du_kien' => 'nullable|date',
        ];

        // Rẽ nhánh validate theo loại khách
        if ($request->is_new_customer == '1') {
            $rules['ten_khach']             = 'required|string|max:100';
            $rules['so_dien_thoai']         = 'required|string|max:20';
            $rules['newkh_tinh_id']         = 'nullable|string|exists:tinh_thanh,id';
            $rules['newkh_quan_id']         = 'nullable|string|exists:quan_huyen,id';
            $rules['newkh_xa_id']           = 'nullable|string|exists:xa_phuong,id';
            $rules['newkh_dia_chi_cu_the']  = 'nullable|string|max:255';
            $rules['latitude']              = 'nullable|numeric';
            $rules['longitude']             = 'nullable|numeric';
        } else {
            $rules['khach_hang_id'] = 'required|exists:khach_hang,id';
        }

        $validated = $request->validate($rules);
        $validated['trang_thai_id'] = $validated['trang_thai_id'] ?? TrangThaiDonHang::CHO_XU_LY;

        // Xử lý khách hàng
        $khachHangId = null;
        if ($request->is_new_customer == '1') {
            $xaId        = $request->newkh_xa_id;
            $diaChiCuThe = $request->newkh_dia_chi_cu_the;
            $lat         = $request->latitude;
            $lng         = $request->longitude;
            $diaChiFull  = $diaChiCuThe;

            if ($xaId) {
                $xa = \App\Models\XaPhuong::with('quanHuyen.tinhThanh')->find($xaId);
                if ($xa) {
                    $lat = $lat ?: $xa->latitude;
                    $lng = $lng ?: $xa->longitude;
                    $parts = array_filter([$diaChiCuThe, $xa->name, $xa->quanHuyen?->name, $xa->quanHuyen?->tinhThanh?->name]);
                    $diaChiFull = implode(', ', $parts);
                }
            }

        // Tự động thêm mới Khách hàng
        $khachHang = KhachHang::create([
            'ten_khach'      => $request->ten_khach,
            'so_dien_thoai'  => $request->so_dien_thoai,
            'tinh_thanh_id'  => $request->newkh_tinh_id,
            'quan_huyen_id'  => $request->newkh_quan_id,
            'xa_phuong_id'   => $request->newkh_xa_id,
            'dia_chi_cu_the' => $diaChiCuThe,
            'latitude'       => $lat,
            'longitude'      => $lng,
        ]);
            $khachHangId = $khachHang->id;
        } else {
            $khachHangId = $request->khach_hang_id;
        }

        $validated['khach_hang_id'] = $khachHangId;

        // Tính lại tiền
        $pricing = app(PricingService::class)->calculateAll($validated);
        $validated['shipping_fee']     = $pricing['shipping_fee'];
        $validated['cod_fee']          = $pricing['cod_fee'];
        $validated['total_collection'] = ($validated['cod_amount'] ?? 0) + $pricing['shipping_fee'];

        // Lọc bớt key không thuộc DonHang
        unset($validated['is_new_customer'], $validated['ten_khach'], $validated['so_dien_thoai'],
              $validated['newkh_tinh_id'], $validated['newkh_quan_id'], $validated['newkh_xa_id'],
              $validated['newkh_dia_chi_cu_the'], $validated['latitude'], $validated['longitude']);

        $trangThaiCu = $donHang->trang_thai_id;
        $donHang->update($validated);

        // Nếu trạng thái thay đổi → ghi lịch sử
        if ($trangThaiCu !== (int) $validated['trang_thai_id']) {
            LichSuTrangThai::create([
                'don_hang_id'    => $donHang->id,
                'trang_thai_id'  => $donHang->trang_thai_id,
                'nguoi_thay_doi' => Auth::id(),
                'thoi_diem'      => now(),
                'ghi_chu'        => $request->input('ly_do_doi_trang_thai', 'Cập nhật trạng thái'),
            ]);
        }

        return redirect()
            ->route('admin.don_hang.index')
            ->with('success', "Đã cập nhật đơn hàng {$donHang->ma_don} thành công!");
    }

    /**
     * Xoá đơn hàng
     */
    public function destroy(DonHang $donHang)
    {
        $maDon = $donHang->ma_don;
        $donHang->delete();

        return redirect()
            ->route('admin.don_hang.index')
            ->with('success', "Đã xoá đơn hàng $maDon thành công!");
    }

    /**
     * Cập nhật nhanh trạng thái đơn hàng từ trang show
     * POST /admin/don-hang/{donHang}/update-status
     */
    public function updateStatus(Request $request, DonHang $donHang)
    {
        $request->validate([
            'trang_thai_id' => 'required|exists:trang_thai_don_hang,id',
            'ghi_chu'       => 'nullable|string|max:300',
        ], [
            'trang_thai_id.required' => 'Vui lòng chọn trạng thái.',
            'trang_thai_id.exists'   => 'Trạng thái không hợp lệ.',
        ]);

        $trangThaiCu  = $donHang->trang_thai_id;
        $trangThaiMoi = (int) $request->trang_thai_id;

        // Nếu trạng thái không đổi → không làm gì
        if ($trangThaiCu === $trangThaiMoi) {
            return redirect()
                ->route('admin.don_hang.show', $donHang)
                ->with('info', 'Trạng thái không thay đổi.');
        }

        // 1. Cập nhật bảng don_hang
        $donHang->update(['trang_thai_id' => $trangThaiMoi]);

        // 2. Ghi lịch sử thay đổi vào lich_su_trang_thai
        LichSuTrangThai::create([
            'don_hang_id'    => $donHang->id,
            'trang_thai_id'  => $trangThaiMoi,
            'nguoi_thay_doi' => Auth::id(),
            'thoi_diem'      => now(),
            'ghi_chu'        => $request->input('ghi_chu', 'Cập nhật trạng thái'),
        ]);

        // 3. Nếu giao thành công -> Xử lý tài chính và KPI
        if ($trangThaiMoi === TrangThaiDonHang::DA_GIAO) {
            app(FinancialService::class)->processCompletedOrder($donHang);
        }

        return redirect()
            ->route('admin.don_hang.show', $donHang)
            ->with('success', "Đã cập nhật trạng thái đơn hàng {$donHang->ma_don}!");
    }

    /**
     * Gán tài xế cho đơn hàng
     */
    public function assignDriver(Request $request, DonHang $donHang)
    {
        $request->validate([
            'tai_xe_id' => 'required|exists:tai_xe,id',
        ], [
            'tai_xe_id.required' => 'Vui lòng chọn tài xế.',
            'tai_xe_id.exists'   => 'Tài xế không hợp lệ.',
        ]);

        $taiXeCu  = $donHang->tai_xe_id;
        $taiXeMoi = $request->tai_xe_id;

        if ((int)$taiXeCu === (int)$taiXeMoi) {
            return back()->with('info', 'Tài xế này đã được gán cho đơn hàng.');
        }

        $updates = ['tai_xe_id' => $taiXeMoi];

        if ((int) $donHang->trang_thai_id === TrangThaiDonHang::CHO_XU_LY) {
            $updates['trang_thai_id'] = TrangThaiDonHang::DA_LAY_HANG;
            $updates['thoi_gian_hoan_thanh'] = null;
        }

        $donHang->update($updates);

        LichSuTrangThai::create([
            'don_hang_id'    => $donHang->id,
            'trang_thai_id'  => $donHang->trang_thai_id,
            'nguoi_thay_doi' => Auth::id(),
            'thoi_diem'      => now(),
            'ghi_chu'        => isset($updates['trang_thai_id'])
                ? 'Gán tài xế và chuyển đơn sang trạng thái đã lấy hàng'
                : 'Gán/thay đổi tài xế phụ trách đơn hàng',
        ]);

        return redirect()
            ->route('admin.don_hang.index')
            ->with('success', "Đã gán tài xế cho đơn hàng {$donHang->ma_don} thành công!");
    }

    /**
     * Đối soát — Chuyển COD từ công nợ tài xế sang ví Shop
     *
     * Logic:
     *  1. Lấy ví tài xế (Wallet của TaiXe) → cộng lại balance (giảm công nợ)
     *  2. Lấy ví Shop → cộng COD vào balance
     *  3. Ghi giao dịch cho cả hai phía
     *  4. Đánh dấu đơn là đã được đối soát
     */
    public function doiSoat(DonHang $donHang)
    {
        // Chỉ cho phép đối soát đơn giao thành công
        if ($donHang->trang_thai_id !== TrangThaiDonHang::DA_GIAO) {
            return back()->with('error', 'Chỉ có thể đối soát đơn hàng đã giao thành công.');
        }

        if ($donHang->da_doi_soat) {
            return back()->with('info', 'Đơn hàng này đã được đối soát rồi.');
        }

        $codAmount   = (float) ($donHang->cod_amount ?? 0);
        $shippingFee = (float) ($donHang->shipping_fee ?? 0);
        $codFee      = (float) ($donHang->cod_fee ?? 0);
        // Số tiền thực trả cho Shop = COD thu được - phí ship - phí COD
        $shopPayout  = $codAmount - $shippingFee - $codFee;

        \Illuminate\Support\Facades\DB::transaction(function () use ($donHang, $codAmount, $shopPayout) {
            // 1. Giảm công nợ tài xế (tài xế nộp lại COD)
            if ($donHang->taiXe) {
                $driverWallet = \App\Models\Wallet::firstOrCreate([
                    'owner_type' => get_class($donHang->taiXe),
                    'owner_id'   => $donHang->taiXe->id,
                ]);
                // Tài xế trả COD —> balance tăng (công nợ giảm)
                $driverWallet->increment('balance', $codAmount);

                \App\Models\Transaction::create([
                    'wallet_id'      => $driverWallet->id,
                    'amount'         => $codAmount,
                    'type'           => 'credit',
                    'reference_type' => 'cod_repayment',
                    'description'    => "Đối soát COD đơn {$donHang->ma_don}",
                ]);
            }

            // 2. Cộng tiền cho Shop
            if ($donHang->shop) {
                $shopWallet = $donHang->shop->getOrCreateWallet();
                $shopWallet->increment('balance', $shopPayout);

                \App\Models\Transaction::create([
                    'wallet_id'      => $shopWallet->id,
                    'amount'         => $shopPayout,
                    'type'           => 'credit',
                    'reference_type' => 'cod_settlement',
                    'description'    => "Đối soát COD đơn {$donHang->ma_don} (sau khi trừ phí)",
                ]);
            }

            // 3. Đánh dấu đơn đã đối soát
            $donHang->update(['da_doi_soat' => true]);
        });

        return redirect()
            ->route('admin.don_hang.show', $donHang)
            ->with('success', "Đã đối soát COD đơn {$donHang->ma_don} thành công!");
    }
}
