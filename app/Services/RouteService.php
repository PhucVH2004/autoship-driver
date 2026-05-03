<?php

namespace App\Services;

use App\Models\DonHang;
use App\Models\TrangThaiDonHang;
use Illuminate\Support\Collection;

/**
 * RouteService
 *
 * Xử lý logic sắp xếp lộ trình tối ưu cho tài xế:
 *   - Lọc đơn hàng chưa hoàn thành trong ngày
 *   - Sắp xếp theo thuật toán Nearest Neighbor
 *   - Tính ước tính km bằng Haversine
 */
class RouteService
{
    /**
     * Lấy danh sách đơn hàng của tài xế trong ngày
     * chưa hoàn thành (bao gồm tồn đọng từ hôm trước)
     */
    public function getPendingOrders(int $taiXeId): Collection
    {
        $done = TrangThaiDonHang::doneStatuses();

        return DonHang::with(['khachHang', 'trangThai', 'shop'])
            ->where('tai_xe_id', $taiXeId)
            ->where(function ($q) use ($done) {
                // Đơn chưa xong (tồn đọng), HOẶC tạo / dự kiến giao hôm nay
                $q->whereNotIn('trang_thai_id', $done)
                  ->orWhere(function ($sub) use ($done) {
                      $sub->whereNotIn('trang_thai_id', $done);
                  })
                  ->orWhereDate('thoi_gian_giao_du_kien', today())
                  ->orWhereDate('created_at', today());
            })
            ->whereNotIn('trang_thai_id', $done)
            ->get();
    }

    /**
     * Sắp xếp danh sách điểm giao theo Nearest Neighbor
     * bắt đầu từ vị trí GPS hiện tại của tài xế
     *
     * @param  float       $startLat   Vĩ độ xuất phát
     * @param  float       $startLng   Kinh độ xuất phát
     * @param  Collection  $orders     Danh sách DonHang chưa giao
     * @return array  [
     *     'sorted'    => DonHang[],   // đã sắp xếp
     *     'waypoints' => [[lat,lng]], // mảng tọa độ (bao gồm điểm xuất phát đầu tiên)
     *     'total_km'  => float,       // tổng ước tính km
     * ]
     */
    public function optimizeRoute(float $startLat, float $startLng, Collection $orders): array
    {
        // Phân loại: đơn có tọa độ vs không có tọa độ
        $withCoords    = $orders->filter(fn($o) => $this->hasCoords($o))->values();
        $withoutCoords = $orders->reject(fn($o) => $this->hasCoords($o))->values();

        $sorted    = [];
        $remaining = $withCoords->values()->all();
        $curLat    = $startLat;
        $curLng    = $startLng;

        // Nearest Neighbor greedy
        while (count($remaining) > 0) {
            $bestIdx  = 0;
            $bestDist = PHP_FLOAT_MAX;

            foreach ($remaining as $i => $order) {
                $lat  = (float) $order->khachHang->latitude;
                $lng  = (float) $order->khachHang->longitude;
                $dist = $this->haversine($curLat, $curLng, $lat, $lng);

                if ($dist < $bestDist) {
                    $bestDist = $dist;
                    $bestIdx  = $i;
                }
            }

            $best   = $remaining[$bestIdx];
            $sorted[] = $best;
            $curLat = (float) $best->khachHang->latitude;
            $curLng = (float) $best->khachHang->longitude;
            array_splice($remaining, $bestIdx, 1);
        }

        // Ghép đơn không có tọa độ vào cuối
        $sorted = array_merge($sorted, $withoutCoords->toArray());

        // Tính tổng km (waypoints bao gồm điểm xuất phát)
        $waypoints = [[$startLat, $startLng]];
        $totalKm   = 0.0;
        $prevLat   = $startLat;
        $prevLng   = $startLng;

        foreach ($sorted as $order) {
            if (!$this->hasCoords($order)) continue;
            $lat = (float) $order->khachHang->latitude;
            $lng = (float) $order->khachHang->longitude;
            $totalKm += $this->haversine($prevLat, $prevLng, $lat, $lng);
            $waypoints[] = [$lat, $lng];
            $prevLat = $lat;
            $prevLng = $lng;
        }

        return [
            'sorted'    => collect($sorted),
            'waypoints' => $waypoints,
            'total_km'  => round($totalKm, 1),
        ];
    }

    /**
     * Tính khoảng cách Haversine giữa 2 điểm (đơn vị: km)
     */
    public function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371; // Bán kính Trái Đất (km)
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Kiểm tra đơn hàng có tọa độ GPS hợp lệ không
     */
    private function hasCoords(DonHang $order): bool
    {
        $kh = $order->khachHang;
        return $kh
            && !empty($kh->latitude)
            && !empty($kh->longitude)
            && ((float) $kh->latitude !== 0.0)
            && ((float) $kh->longitude !== 0.0);
    }

    public function orderBySequence(Collection $orders, array $orderedIds): Collection
    {
        if (empty($orderedIds)) {
            return $orders->values();
        }

        $positions = array_flip($orderedIds);

        return $orders->sortBy(function ($order) use ($positions) {
            return $positions[$order->id] ?? PHP_INT_MAX;
        })->values();
    }

    public function findNextStop(Collection $orders): ?DonHang
    {
        return $orders->first(function (DonHang $order) {
            return !in_array($order->trang_thai_id, TrangThaiDonHang::doneStatuses(), true);
        });
    }

    public function buildRouteSummary(Collection $orders, ?DonHang $nextStop = null): array
    {
        $completed = $orders->filter(fn (DonHang $order) => in_array($order->trang_thai_id, TrangThaiDonHang::successfulStatuses(), true))->count();
        $failed = $orders->filter(fn (DonHang $order) => in_array($order->trang_thai_id, [TrangThaiDonHang::HUY, TrangThaiDonHang::DA_HOAN], true))->count();
        $remaining = $orders->filter(fn (DonHang $order) => !in_array($order->trang_thai_id, TrangThaiDonHang::doneStatuses(), true))->count();

        return [
            'total' => $orders->count(),
            'completed' => $completed,
            'failed' => $failed,
            'remaining' => $remaining,
            'next_stop_id' => $nextStop?->id,
        ];
    }

    /**
     * Build payload JSON để truyền xuống Blade (deliveries array)
     */
    public function buildDeliveriesJson(Collection $orders, ?int $nextStopId = null): array
    {
        return $orders->map(function ($dh, $idx) use ($nextStopId) {
            $kh     = $dh->khachHang;
            $lat    = (float) ($kh?->latitude  ?? 0);
            $lng    = (float) ($kh?->longitude ?? 0);
            $addr   = $kh?->dia_chi ?? '';
            $status = $dh->trangThai?->ten_trang_thai ?? '';
            $isDone = in_array($dh->trang_thai_id, TrangThaiDonHang::doneStatuses(), true);

            if ($lat && $lng) {
                $navUrl = "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";
            } elseif ($addr) {
                $navUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($addr);
            } else {
                $navUrl = null;
            }

            return [
                'id'            => $dh->id,
                'ma_don'        => $dh->ma_don,
                'ten_khach'     => $kh?->ten_khach ?? 'Khách hàng',
                'so_dien_thoai' => $kh?->so_dien_thoai ?? '',
                'dia_chi'       => $addr ?: '—',
                'lat'           => $lat,
                'lng'           => $lng,
                'trang_thai_id' => $dh->trang_thai_id,
                'trang_thai'    => $status ?: 'Chờ giao',
                'is_done'       => $isDone,
                'is_next'       => $nextStopId !== null && $dh->id === $nextStopId,
                'cod_amount'    => (float) ($dh->cod_amount ?? 0),
                'nav_url'       => $navUrl,
                'has_coords'    => ($lat !== 0.0 && $lng !== 0.0),
            ];
        })->values()->toArray();
    }
}
