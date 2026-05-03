<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;

class DashboardController extends Controller
{
    /**
     * Dashboard — thống kê thật từ DB
     */
    public function index()
    {
        // ── Thống kê đơn hàng ──────────────────────────────────────────────

        // Tổng đơn tạo hôm nay
        $tongDonHomNay = DonHang::whereDate('created_at', today())->count();

        $donDangGiao = DonHang::where('trang_thai_id', TrangThaiDonHang::DANG_GIAO)->count();

        $donHoanThanhHomNay = DonHang::where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
            ->whereDate('updated_at', today())
            ->count();

        // ── Tài xế ─────────────────────────────────────────────────────────
        $taiXeHoatDong = TaiXe::whereIn('trang_thai', ['Ranh', 'Dang giao'])->count();

        // ── Đơn hàng gần đây (5 đơn mới nhất cho dashboard table) ──────────
        $donHangGanDay = DonHang::with(['khachHang', 'taiXe', 'trangThai'])
            ->latest()
            ->limit(5)
            ->get();

        // ── Thống kê 7 ngày gần đây cho biểu đồ Chart.js ──────────────────
        $chartData = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label'       => $date->format('d/m'),
                'tong'        => DonHang::whereDate('created_at', $date)->count(),
                'hoan_thanh'  => DonHang::where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                    ->whereDate('updated_at', $date)
                    ->count(),
            ];
        });

        // Gộp lại để truyền vào view
        $thong_ke = [
            'tong_don_hom_nay'    => $tongDonHomNay,
            'don_dang_giao'       => $donDangGiao,
            'don_hoan_thanh'      => $donHoanThanhHomNay,
            'tai_xe_hoat_dong'    => $taiXeHoatDong,
        ];

        return view('admin.dashboard.index', compact(
            'thong_ke',
            'donHangGanDay',
            'chartData'
        ));
    }
}
