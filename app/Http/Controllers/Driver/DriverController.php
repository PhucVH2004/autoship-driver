<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    /**
     * Dashboard tài xế — thống kê nhanh đơn hôm nay
     */
    public function dashboard()
    {
        $taiXeId = Auth::user()->taiXe?->id;

        // Tổng đơn hôm nay được phân công
        $baseQuery = DonHang::whereDate('created_at', today());
        if ($taiXeId) {
            $baseQuery->where('tai_xe_id', $taiXeId);
        }

        $tongDonHomNay = (clone $baseQuery)->count();

        // Đang giao — trạng thái chứa chữ "giao" / "đang"
        $dangGiao = (clone $baseQuery)
            ->whereHas('trangThai', fn($q) =>
                $q->whereRaw("LOWER(ten_trang_thai) LIKE '%giao%'")
            )->count();

        // Đã hoàn thành — trạng thái chứa "thành"
        $daHoanThanh = (clone $baseQuery)
            ->whereHas('trangThai', fn($q) =>
                $q->whereRaw("LOWER(ten_trang_thai) LIKE '%th%nh%'")
            )->count();

        return view('driver.dashboard', compact(
            'tongDonHomNay',
            'dangGiao',
            'daHoanThanh'
        ));
    }

    /**
     * Danh sách đơn hàng được phân công cho tài xế này
     */
    public function orders()
    {
        $taiXeId = Auth::user()->taiXe?->id;

        $query = DonHang::with(['khachHang', 'trangThai', 'taiXe'])
                        ->latest();

        if ($taiXeId) {
            $query->where('tai_xe_id', $taiXeId);
        }

        // Lọc theo trạng thái (nếu có)
        if (request('trang_thai')) {
            $filter = request('trang_thai');
            $query->whereHas('trangThai', fn($q) =>
                $q->whereRaw("LOWER(ten_trang_thai) LIKE ?", ["%{$filter}%"])
            );
        }

        $donHangs = $query->paginate(15)->withQueryString();

        return view('driver.orders.index', compact('donHangs'));
    }

    /**
     * Chi tiết đơn hàng — chỉ xem được đơn được phân công cho mình
     */
    public function orderDetail(DonHang $donHang)
    {
        $taiXeId = Auth::user()->taiXe?->id;

        // Bảo vệ: tài xế chỉ xem đơn của mình
        if ($taiXeId && $donHang->tai_xe_id !== $taiXeId) {
            abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        $donHang->load([
            'khachHang',
            'taiXe',
            'trangThai',
            'lichSuTrangThais.trangThai',
        ]);

        return view('driver.orders.show', compact('donHang'));
    }

    /**
     * Bản đồ lộ trình — các đơn có toạ độ (chưa hoàn thành)
     */
    public function route()
    {
        return redirect()->route('driver.route.today');
    }
}
