<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\RouteSession;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;
use App\Services\RouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverRouteController extends Controller
{
    public function __construct(private RouteService $routeService) {}

    /**
     * Trang lộ trình hôm nay
     * - Lấy đơn chưa giao xong
     * - Server-side: nếu tài xế đã có GPS trong DB → tính sắp xếp ngay
     * - Client-side: khi nhấn "Vị trí & Lộ trình" → gọi browser Geolocation → gọi /driver/route/optimize
     */
    public function today()
    {
        $taiXe = Auth::user()->taiXe;
        $taiXeId = $taiXe?->id;
        $done = TrangThaiDonHang::doneStatuses();

        $allToday = DonHang::with(['trangThai'])
            ->where('tai_xe_id', $taiXeId)
            ->where(function ($q) use ($done) {
                $q->whereNotIn('trang_thai_id', $done)
                    ->orWhereDate('thoi_gian_giao_du_kien', today())
                    ->orWhereDate('created_at', today())
                    ->orWhereDate('updated_at', today());
            })
            ->get();

        $pendingOrders = $this->routeService->getPendingOrders($taiXeId);
        $session = RouteSession::query()->forDriver($taiXeId)->forDate(today())->first();
        $optimizedResult = null;
        $taiXeLat = (float) ($taiXe?->current_lat ?? 0);
        $taiXeLng = (float) ($taiXe?->current_lng ?? 0);

        if ($session && !empty($session->orderedIds())) {
            $pendingOrders = $this->routeService->orderBySequence($pendingOrders, $session->orderedIds());
        } elseif ($taiXeLat && $taiXeLng && $pendingOrders->isNotEmpty()) {
            $optimizedResult = $this->routeService->optimizeRoute($taiXeLat, $taiXeLng, $pendingOrders);
            $pendingOrders = $optimizedResult['sorted'];
        }

        $nextStop = $this->routeService->findNextStop($pendingOrders);
        $routeSummary = $this->routeService->buildRouteSummary($pendingOrders, $nextStop);
        $deliveries = $this->routeService->buildDeliveriesJson($pendingOrders, $nextStop?->id);

        return view('driver.route.today', [
            'deliveries' => $deliveries,
            'nextStop' => $nextStop,
            'routeSummary' => $routeSummary,
            'session' => $session,
            'tongHomNay' => $allToday->count(),
            'daHoanThanh' => $allToday->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)->count(),
            'daHuy' => $allToday->where('trang_thai_id', TrangThaiDonHang::HUY)->count(),
            'dangGiao' => $allToday->whereIn('trang_thai_id', [TrangThaiDonHang::DA_LAY_HANG, TrangThaiDonHang::DANG_GIAO, TrangThaiDonHang::HOAN])->count(),
            'conLai' => $allToday->whereNotIn('trang_thai_id', $done)->count(),
            'taiXeLat' => $taiXeLat,
            'taiXeLng' => $taiXeLng,
            'optimizedResult' => $optimizedResult,
        ]);
    }

    /**
     * API: Tối ưu lộ trình từ vị trí GPS tài xế gửi lên
     * POST /driver/route/optimize
     * Body: { lat: float, lng: float }
     * Response JSON: { sorted: [...], waypoints: [...], total_km: float }
     */
    public function optimize(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $taiXe   = Auth::user()->taiXe;
        $taiXeId = $taiXe?->id;

        // Lưu vị trí hiện tại vào DB để server-side biết khi reload
        if ($taiXe) {
            $taiXe->update([
                'current_lat' => $request->lat,
                'current_lng' => $request->lng,
                'last_update' => now(),
            ]);
        }

        $pendingOrders   = $this->routeService->getPendingOrders($taiXeId);
        $optimizedResult = $this->routeService->optimizeRoute(
            (float) $request->lat,
            (float) $request->lng,
            $pendingOrders
        );

        // Lưu phiên lộ trình vào route_sessions
        $session = RouteSession::getOrCreateToday($taiXeId);
        $session->updateFromOptimize(
            $optimizedResult['sorted']->pluck('id')->toArray(),
            $optimizedResult['total_km'],
            (float) $request->lat,
            (float) $request->lng,
        );

        $nextStop = $this->routeService->findNextStop($optimizedResult['sorted']);

        return response()->json([
            'sorted'    => $this->routeService->buildDeliveriesJson($optimizedResult['sorted'], $nextStop?->id),
            'waypoints' => $optimizedResult['waypoints'],
            'total_km'  => $optimizedResult['total_km'],
        ]);
    }

    /**
     * API: Cập nhật vị trí GPS tài xế (gọi mỗi N giây từ client)
     * POST /driver/route/update-position
     * Body: { lat, lng }
     */
    public function updatePosition(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $taiXe = Auth::user()->taiXe;
        if ($taiXe) {
            $taiXe->update([
                'current_lat' => $request->lat,
                'current_lng' => $request->lng,
                'last_update' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
