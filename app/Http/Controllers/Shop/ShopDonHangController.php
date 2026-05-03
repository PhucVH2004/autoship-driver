<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\KhachHang;
use App\Models\TrangThaiDonHang;
use App\Models\XaPhuong;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopDonHangController extends Controller
{
    public function __construct(private PricingService $pricing) {}

    /**
     * Danh sách đơn của Shop
     */
    public function index(Request $request)
    {
        $shop = Auth::user()->shop;
        $trangThais = TrangThaiDonHang::orderBy('id')->get();

        if (!$shop) {
            return view('shop.don_hang.index', [
                'donHangs' => collect(),
                'shop' => null,
                'trangThais' => $trangThais,
                'counts' => ['tat_ca' => 0],
            ]);
        }

        $query = DonHang::with(['khachHang', 'trangThai'])
            ->where('sender_id', $shop->id)
            ->latest();

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai_id', $request->trang_thai);
        }

        $donHangs = $query->paginate(20)->withQueryString();

        $counts = ['tat_ca' => DonHang::where('sender_id', $shop->id)->count()];
        foreach ($trangThais as $tt) {
            $counts[$tt->id] = DonHang::where('sender_id', $shop->id)
                ->where('trang_thai_id', $tt->id)
                ->count();
        }

        return view('shop.don_hang.index', compact('donHangs', 'shop', 'trangThais', 'counts'));
    }

    /**
     * Form tạo đơn hàng mới
     */
    public function create()
    {
        $shop = Auth::user()->shop;
        return view('shop.don_hang.create', compact('shop'));
    }

    /**
     * Lưu đơn hàng mới
     */
    public function store(Request $request)
    {
        $shop = Auth::user()->shop;
        if (!$shop) {
            return redirect()->route('shop.dashboard')->with('error', 'Bạn chưa có thông tin Shop.');
        }

        $validated = $this->validateOrderData($request);
        $khachHang = $this->upsertCustomerFromValidated($validated);
        $pricing = $this->pricing->calculateAll($validated);

        $donHang = DB::transaction(function () use ($validated, $khachHang, $shop, $pricing) {
            $dh = DonHang::create([
                'ma_don' => '',
                'khach_hang_id' => $khachHang->id,
                'sender_id' => $shop->id,
                'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
                'weight' => $validated['weight'] ?? 0,
                'length' => $validated['length'] ?? 0,
                'width' => $validated['width'] ?? 0,
                'height' => $validated['height'] ?? 0,
                'cod_amount' => $validated['cod_amount'] ?? 0,
                'delivery_type' => $validated['delivery_type'],
                'shipping_fee' => $pricing['shipping_fee'],
                'ghi_chu' => $validated['ghi_chu'] ?? null,
            ]);

            $dh->update(['ma_don' => DonHang::generateMaDon($dh->id)]);

            return $dh;
        });

        return redirect()
            ->route('shop.don_hang.show', $donHang->id)
            ->with('success', "Đã tạo đơn hàng {$donHang->ma_don} thành công!");
    }

    /**
     * Form chỉnh sửa đơn hàng
     */
    public function edit(int $id)
    {
        $shop = Auth::user()->shop;
        $donHang = $this->findShopOrder($shop?->id, $id);

        $donHang->load(['khachHang', 'trangThai']);

        return view('shop.don_hang.edit', compact('donHang', 'shop'));
    }

    /**
     * Cập nhật đơn hàng
     */
    public function update(Request $request, int $id)
    {
        $shop = Auth::user()->shop;
        $donHang = $this->findShopOrder($shop?->id, $id);

        $validated = $this->validateOrderData($request);
        $khachHang = $this->upsertCustomerFromValidated($validated);
        $pricing = $this->pricing->calculateAll($validated);

        $donHang->update([
            'khach_hang_id' => $khachHang->id,
            'weight' => $validated['weight'] ?? 0,
            'length' => $validated['length'] ?? 0,
            'width' => $validated['width'] ?? 0,
            'height' => $validated['height'] ?? 0,
            'cod_amount' => $validated['cod_amount'] ?? 0,
            'delivery_type' => $validated['delivery_type'],
            'shipping_fee' => $pricing['shipping_fee'],
            'ghi_chu' => $validated['ghi_chu'] ?? null,
        ]);

        return redirect()
            ->route('shop.don_hang.show', $donHang->id)
            ->with('success', "Đã cập nhật đơn hàng {$donHang->ma_don} thành công!");
    }

    /**
     * Xóa đơn hàng
     */
    public function destroy(int $id)
    {
        $shop = Auth::user()->shop;
        $donHang = $this->findShopOrder($shop?->id, $id);

        $maDon = $donHang->ma_don;
        $donHang->delete();

        return redirect()
            ->route('shop.don_hang.index')
            ->with('success', "Đã xoá đơn hàng {$maDon} thành công!");
    }

    /**
     * Chi tiết một đơn hàng
     */
    public function show(int $id)
    {
        $shop = Auth::user()->shop;
        $donHang = DonHang::with(['khachHang', 'trangThai', 'taiXe', 'lichSuTrangThais.trangThai', 'lichSuTrangThais.nguoiThayDoi'])
            ->where('sender_id', $shop?->id)
            ->findOrFail($id);

        return view('shop.don_hang.show', compact('donHang', 'shop'));
    }

    private function validateOrderData(Request $request): array
    {
        $validated = $request->validate([
            'ten_nguoi_nhan' => 'required|string|max:100',
            'sdt_nguoi_nhan' => 'required|string|max:20',
            'newkh_tinh_id' => 'required|integer|exists:tinh_thanh,id',
            'newkh_quan_id' => 'required|integer|exists:quan_huyen,id',
            'newkh_xa_id' => 'required|integer|exists:xa_phuong,id',
            'newkh_dia_chi_cu_the' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'weight' => 'nullable|integer|min:0',
            'length' => 'nullable|integer|min:0',
            'width' => 'nullable|integer|min:0',
            'height' => 'nullable|integer|min:0',
            'cod_amount' => 'nullable|numeric|min:0',
            'ghi_chu' => 'nullable|string|max:500',
            'delivery_type' => 'nullable|in:standard,fast,urgent',
        ]);

        $validated['delivery_type'] = $validated['delivery_type'] ?? 'standard';

        return $validated;
    }

    private function upsertCustomerFromValidated(array $validated): KhachHang
    {
        $xa = XaPhuong::with('quanHuyen.tinhThanh')->find($validated['newkh_xa_id']);
        $lat = $validated['latitude'] ?? null;
        $lng = $validated['longitude'] ?? null;
        $diaChiCuThe = trim((string) ($validated['newkh_dia_chi_cu_the'] ?? ''));
        $diaChiFull = $diaChiCuThe;

        if ($xa) {
            $lat = $lat ?: $xa->latitude;
            $lng = $lng ?: $xa->longitude;
            $parts = array_filter([
                $diaChiCuThe,
                $xa->name,
                $xa->quanHuyen?->name,
                $xa->quanHuyen?->tinhThanh?->name,
            ]);
            $diaChiFull = implode(', ', $parts);
        }

        return KhachHang::updateOrCreate(
            ['so_dien_thoai' => $validated['sdt_nguoi_nhan']],
            [
                'ten_khach' => $validated['ten_nguoi_nhan'],
                'tinh_thanh_id' => $validated['newkh_tinh_id'],
                'quan_huyen_id' => $validated['newkh_quan_id'],
                'xa_phuong_id' => $validated['newkh_xa_id'],
                'dia_chi_cu_the' => $diaChiCuThe ?: null,
                'dia_chi' => $diaChiFull ?: '',
                'latitude' => $lat,
                'longitude' => $lng,
            ]
        );
    }

    private function findShopOrder(?int $shopId, int $id): DonHang
    {
        return DonHang::where('sender_id', $shopId)->findOrFail($id);
    }
}
