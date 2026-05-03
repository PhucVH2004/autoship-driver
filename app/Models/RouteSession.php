<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RouteSession — Phiên lộ trình giao hàng hàng ngày của tài xế.
 *
 * Mỗi record = 1 tài xế × 1 ngày.
 * Lưu: thứ tự đơn đã tối ưu, thống kê giao hàng, ước tính km.
 */
class RouteSession extends Model
{
    protected $table = 'route_sessions';

    protected $fillable = [
        'tai_xe_id',
        'route_date',
        'start_lat',
        'start_lng',
        'order_sequence',
        'total_orders',
        'completed_orders',
        'failed_orders',
        'total_km',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'route_date'      => 'date',
        'order_sequence'  => 'array',
        'total_km'        => 'float',
        'start_lat'       => 'float',
        'start_lng'       => 'float',
        'started_at'      => 'datetime',
        'finished_at'     => 'datetime',
    ];

    // ─── RELATIONSHIPS ──────────────────────────────────────────────

    public function taiXe(): BelongsTo
    {
        return $this->belongsTo(TaiXe::class, 'tai_xe_id');
    }

    // ─── SCOPES ─────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForDriver($query, int $taiXeId)
    {
        return $query->where('tai_xe_id', $taiXeId);
    }

    public function scopeForDate($query, $date = null)
    {
        return $query->where('route_date', $date ?? today()->toDateString());
    }

    // ─── HELPERS ────────────────────────────────────────────────────

    /**
     * Lấy hoặc tạo phiên lộ trình hôm nay cho tài xế
     */
    public static function getOrCreateToday(int $taiXeId): self
    {
        return self::firstOrCreate([
            'tai_xe_id'  => $taiXeId,
            'route_date' => today()->toDateString(),
        ], [
            'status'     => 'active',
            'started_at' => now(),
        ]);
    }

    /**
     * Cập nhật thống kê khi optimize xong
     */
    public function updateFromOptimize(array $orderedIds, float $totalKm, float $lat, float $lng): void
    {
        $this->update([
            'order_sequence' => $orderedIds,
            'total_orders'   => count($orderedIds),
            'total_km'       => $totalKm,
            'start_lat'      => $lat,
            'start_lng'      => $lng,
        ]);
    }

    /**
     * Đánh dấu phiên hoàn thành
     */
    public function markCompleted(int $completed, int $failed): void
    {
        $this->update([
            'status'           => 'completed',
            'completed_orders' => $completed,
            'failed_orders'    => $failed,
            'finished_at'      => now(),
        ]);
    }

    public function orderedIds(): array
    {
        return collect($this->order_sequence ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function syncOrderSequence(array $orderedIds): void
    {
        $orderedIds = collect($orderedIds)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->update([
            'order_sequence' => $orderedIds,
            'total_orders' => count($orderedIds),
        ]);
    }

    public function removeOrder(int $orderId): void
    {
        $this->syncOrderSequence(array_values(array_filter(
            $this->orderedIds(),
            fn (int $id) => $id !== $orderId
        )));
    }

    public function appendOrder(int $orderId): void
    {
        $orderedIds = $this->orderedIds();

        if (!in_array($orderId, $orderedIds, true)) {
            $orderedIds[] = $orderId;
        }

        $this->syncOrderSequence($orderedIds);
    }

    public function moveOrder(int $orderId, string $direction): void
    {
        $orderedIds = $this->orderedIds();
        $currentIndex = array_search($orderId, $orderedIds, true);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if (!array_key_exists($targetIndex, $orderedIds)) {
            return;
        }

        [$orderedIds[$currentIndex], $orderedIds[$targetIndex]] = [$orderedIds[$targetIndex], $orderedIds[$currentIndex]];

        $this->syncOrderSequence($orderedIds);
    }

    public function remainingOrdersCount(): int
    {
        return max(0, (int) $this->total_orders - (int) $this->completed_orders - (int) $this->failed_orders);
    }

    public function displayStatus(): string
    {
        if ($this->status === 'completed') {
            return 'Đã xong';
        }

        if ((int) $this->total_orders === 0) {
            return 'Chưa bắt đầu';
        }

        return $this->remainingOrdersCount() > 0 ? 'Đang giao' : 'Đã xong';
    }
}
