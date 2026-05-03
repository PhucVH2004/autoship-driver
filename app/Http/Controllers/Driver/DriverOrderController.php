<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\LichSuTrangThai;
use App\Models\TrangThaiDonHang;
use App\Services\DeliveryFeeService;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DriverOrderController extends Controller
{
    /**
     * Danh sách đơn hàng của tài xế
     */
    public function index(Request $request)
    {
        $taiXeId = Auth::user()->taiXe?->id;

        $query = DonHang::with(['khachHang', 'trangThai'])
            ->where('tai_xe_id', $taiXeId)
            ->latest();

        if ($request->has('trang_thai')) {
            $filterId = (int) $request->trang_thai;
            if ($filterId > 0) {
                $query->where('trang_thai_id', $filterId);
            }
        }

        $donHangs = $query->paginate(15)->withQueryString();

        return view('driver.orders.index', compact('donHangs'));
    }

    /**
     * Chi tiết đơn hàng — YC2 (chỉ đường), YC3 (nút cập nhật), YC7 (upload ảnh)
     */
    public function show($id)
    {
        $taiXeId = Auth::user()->taiXe?->id;

        $donHang = DonHang::with([
            'khachHang',
            'trangThai',
            'lichSuTrangThais.trangThai'
        ])->findOrFail($id);

        // Bảo vệ: tài xế chỉ xem được đơn của mình
        if ($donHang->tai_xe_id !== $taiXeId) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        // Lấy danh sách trạng thái tài xế được phép chuyển
        $trangThaiOptions = TrangThaiDonHang::whereIn('id', [
            TrangThaiDonHang::DA_LAY_HANG,
            TrangThaiDonHang::DANG_GIAO,
            TrangThaiDonHang::DA_GIAO,
            TrangThaiDonHang::HUY,
            TrangThaiDonHang::HOAN,
            TrangThaiDonHang::DA_HOAN,
        ])->orderBy('thu_tu')->get();

        if ($trangThaiOptions->isEmpty()) {
            $trangThaiOptions = TrangThaiDonHang::all();
        }

        $fee = app(DeliveryFeeService::class)->getBreakdown(
            (float) ($donHang->shipping_fee ?? 0),
            (float) ($donHang->cod_fee ?? 0),
        );

        return view('driver.orders.show', compact('donHang', 'trangThaiOptions', 'fee'));
    }

    /**
     * Cập nhật trạng thái đơn — YC3
     * Hỗ trợ quick-action (Nhận đơn, Đang giao, Hoàn thành, Giao thất bại)
     */
    public function updateStatus(Request $request, $id)
    {
        $taiXeId = Auth::user()->taiXe?->id;
        $donHang = DonHang::findOrFail($id);

        if ($donHang->tai_xe_id !== $taiXeId) {
            abort(403, 'Bạn không có quyền cập nhật đơn hàng này.');
        }

        $request->validate([
            'trang_thai_id' => 'required|exists:trang_thai_don_hang,id',
            'ghi_chu'       => 'nullable|string|max:500',
        ]);

        $trangThaiMoi = TrangThaiDonHang::findOrFail($request->trang_thai_id);

        $trangThaiMoiId = (int) $request->trang_thai_id;
        $donHang->trang_thai_id = $trangThaiMoiId;

        // Đánh dấu thời gian hoàn thành khi đơn đi vào trạng thái kết thúc
        if (in_array($trangThaiMoiId, TrangThaiDonHang::doneStatuses(), true)) {
            $donHang->thoi_gian_hoan_thanh = now();
        } else {
            $donHang->thoi_gian_hoan_thanh = null;
        }

        DB::transaction(function () use ($donHang, $taiXeId, $trangThaiMoiId, $request) {
            $donHang->save();

            // Lưu lịch sử
            LichSuTrangThai::create([
                'don_hang_id'   => $donHang->id,
                'trang_thai_id' => $trangThaiMoiId,
                'nguoi_thay_doi'=> Auth::id(),
                'thoi_diem'     => now(),
                'ghi_chu'       => $request->ghi_chu,
            ]);

            // Khi giao thành công => tự tính phí + cộng ví + tạo transaction
            if ($trangThaiMoiId === TrangThaiDonHang::DA_GIAO) {
                app(FinancialService::class)->processCompletedOrder($donHang);
            }
        });

        return redirect()
            ->route('driver.orders.show', $donHang->id)
            ->with('success', 'Đã cập nhật trạng thái: ' . $trangThaiMoi->ten_trang_thai);
    }

    /**
     * Tự động xử lý tài chính khi đơn hoàn thành (deprecated — now handled by FinancialService)
     */
    private function handleCompletedOrderFinance(DonHang $donHang, int $taiXeId): void
    {
        // Phế truất logic cũ, FinancialService đã thay thế
    }

    /**
     * Upload ảnh xác nhận giao hàng — YC7
     * POST /driver/orders/{id}/upload-photo
     */
    public function uploadPhoto(Request $request, $id)
    {
        $taiXeId = Auth::user()->taiXe?->id;
        $donHang = DonHang::findOrFail($id);

        if ($donHang->tai_xe_id !== $taiXeId) {
            abort(403, 'Bạn không có quyền cập nhật đơn hàng này.');
        }

        $request->validate([
            'delivery_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'delivery_photo.required' => 'Vui lòng chọn ảnh.',
            'delivery_photo.image'    => 'File phải là hình ảnh.',
            'delivery_photo.mimes'    => 'Chỉ chấp nhận JPEG, PNG, JPG, WEBP.',
            'delivery_photo.max'      => 'Ảnh không được vượt quá 5MB.',
        ]);

        if ($donHang->delivery_photo && Storage::disk('public')->exists($donHang->delivery_photo)) {
            Storage::disk('public')->delete($donHang->delivery_photo);
        }

        $path = $request->file('delivery_photo')->store('delivery_proofs', 'public');

        $donHang->delivery_photo = $path;
        $donHang->save();

        return redirect()
            ->route('driver.orders.show', $donHang->id)
            ->with('success', 'Đã upload ảnh xác nhận giao hàng thành công.');
    }
}
