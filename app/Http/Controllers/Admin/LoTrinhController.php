<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\RouteSession;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;
use App\Services\AdminRouteDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoTrinhController extends Controller
{
    public function __construct(private AdminRouteDispatchService $dispatchService) {}

    /**
     * Danh sách tài xế với thống kê đơn hàng trong ngày
     */
    public function index(Request $request)
    {
        $routeDate = $request->input('date', today()->toDateString());

        $taiXes = TaiXe::query()
            ->with([
                'donHangs' => fn ($query) => $query
                    ->with(['trangThai'])
                    ->whereDate('created_at', $routeDate),
            ])
            ->orderBy('ho_ten')
            ->get()
            ->map(function (TaiXe $taiXe) {
                $orders = $taiXe->donHangs;

                return [
                    'id' => $taiXe->id,
                    'ho_ten' => $taiXe->ho_ten,
                    'bien_so_xe' => $taiXe->bien_so_xe,
                    'trang_thai' => $taiXe->trang_thai,
                    'trang_thai_label' => $taiXe->trang_thai_label,
                    'trang_thai_class' => $taiXe->trang_thai_class,
                    'total_orders' => $orders->count(),
                    'completed_orders' => $orders->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)->count(),
                    'pending_orders' => $orders->whereNotIn('trang_thai_id', [
                        TrangThaiDonHang::DA_GIAO,
                        TrangThaiDonHang::HUY,
                        TrangThaiDonHang::HOAN,
                        TrangThaiDonHang::DA_HOAN,
                    ])->count(),
                ];
            });

        return view('admin.lo_trinh.index', [
            'routeDate' => $routeDate,
            'taiXes' => $taiXes,
        ]);
    }

    /**
     * API endpoint: Lấy dữ liệu lộ trình của 1 tài xế trong ngày cụ thể
     */
    public function show(Request $request, int $taiXeId)
    {
        $date = $request->input('date', today()->toDateString());
        $isToday = $date === today()->toDateString();

        $taiXe = TaiXe::query()
            ->with(['viTriMoiNhat'])
            ->findOrFail($taiXeId);

        $orders = DonHang::query()
            ->with(['khachHang', 'trangThai'])
            ->where('tai_xe_id', $taiXeId)
            ->whereDate('created_at', $date)
            ->orderByRaw("
                CASE
                    WHEN trang_thai_id = ? THEN 1
                    WHEN trang_thai_id NOT IN (?, ?, ?, ?, ?) THEN 2
                    ELSE 3
                END
            ", [
                TrangThaiDonHang::DANG_GIAO,
                TrangThaiDonHang::DA_GIAO,
                TrangThaiDonHang::HUY,
                TrangThaiDonHang::HOAN,
                TrangThaiDonHang::DA_HOAN,
                TrangThaiDonHang::CHO_XU_LY,
            ])
            ->get()
            ->map(function (DonHang $order) {
                return [
                    'id' => $order->id,
                    'ma_don' => $order->ma_don,
                    'khach_hang' => [
                        'ten_khach' => $order->khachHang?->ten_khach,
                        'dia_chi' => $order->khachHang?->dia_chi,
                        'latitude' => $order->khachHang?->latitude,
                        'longitude' => $order->khachHang?->longitude,
                    ],
                    'trang_thai' => [
                        'id' => $order->trang_thai_id,
                        'ten_trang_thai' => $order->trangThai?->ten_trang_thai,
                    ],
                    'cod_amount' => $order->cod_amount,
                    'is_current' => $order->trang_thai_id === TrangThaiDonHang::DANG_GIAO,
                ];
            });

        $statistics = [
            'total' => $orders->count(),
            'completed' => $orders->where('trang_thai.id', TrangThaiDonHang::DA_GIAO)->count(),
            'pending' => $orders->whereNotIn('trang_thai.id', [
                TrangThaiDonHang::DA_GIAO,
                TrangThaiDonHang::HUY,
                TrangThaiDonHang::HOAN,
                TrangThaiDonHang::DA_HOAN,
            ])->count(),
        ];

        return response()->json([
            'driver' => [
                'id' => $taiXe->id,
                'ho_ten' => $taiXe->ho_ten,
                'bien_so_xe' => $taiXe->bien_so_xe,
                'trang_thai' => $taiXe->trang_thai_label,
                'current_lat' => $taiXe->current_lat,
                'current_lng' => $taiXe->current_lng,
                'last_update' => $taiXe->last_update?->format('Y-m-d H:i:s'),
            ],
            'orders' => $orders->values(),
            'statistics' => $statistics,
            'show_current_location' => $isToday,
        ]);
    }

}
