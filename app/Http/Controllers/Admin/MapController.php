<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\KhachHang;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;

class MapController extends Controller
{
    /**
     * Trang bản đồ — trả dữ liệu thật từ DB:
     * - Danh sách tài xế + vị trí GPS mới nhất
     * - Vị trí khách hàng có đơn đang giao
     * - Đơn hàng đang giao
     */
    public function index()
    {
        // ── Tài xế đang hoạt động (Ranh hoặc Dang giao) + vị trí mới nhất ──
        $taiXes = TaiXe::whereIn('trang_thai', ['Ranh', 'Dang giao'])
            ->with('viTriMoiNhat')    // lấy bản ghi lo_trinh mới nhất
            ->get()
            ->map(function ($tx) {
                return [
                    'id'          => $tx->id,
                    'ho_ten'      => $tx->ho_ten,
                    'bien_so_xe'  => $tx->bien_so_xe,
                    'trang_thai'  => $tx->trang_thai_label,
                    'status_class'=> $tx->trang_thai_class,
                    'latitude'    => $tx->viTriMoiNhat?->latitude,
                    'longitude'   => $tx->viTriMoiNhat?->longitude,
                    'cap_nhat'    => $tx->viTriMoiNhat?->thoi_gian?->format('H:i d/m'),
                ];
            });

        // ── Đơn hàng đang giao ──
        $donHangDangGiao = DonHang::with(['khachHang', 'taiXe', 'trangThai'])
            ->where('trang_thai_id', TrangThaiDonHang::DANG_GIAO)
            ->get();

        // ── Vị trí khách hàng có đơn đang giao (để vẽ marker điểm đến) ──
        $khachHangIds = $donHangDangGiao->pluck('khach_hang_id')->unique();
        $khachHangs   = KhachHang::whereIn('id', $khachHangIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('admin.map.index', compact('taiXes', 'donHangDangGiao', 'khachHangs'));
    }
}
