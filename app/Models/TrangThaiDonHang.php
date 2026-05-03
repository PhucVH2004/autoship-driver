<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrangThaiDonHang extends Model
{
    public const CHO_XU_LY = 1;
    public const DA_LAY_HANG = 2;
    public const DANG_GIAO = 3;
    public const DA_GIAO = 4;
    public const HUY = 5;
    public const HOAN = 6;
    public const DA_HOAN = 7;

    // Alias giữ tương thích với code cũ
    public const HOAN_THANH = self::DA_GIAO;

    protected $table = 'trang_thai_don_hang';

    public $timestamps = false;

    protected $fillable = ['ten_trang_thai'];

    public static function doneStatuses(): array
    {
        return [self::DA_GIAO, self::HUY, self::DA_HOAN];
    }

    public static function successfulStatuses(): array
    {
        return [self::DA_GIAO];
    }

    public static function activeStatuses(): array
    {
        return [self::CHO_XU_LY, self::DA_LAY_HANG, self::DANG_GIAO, self::HOAN];
    }

    /** Một trạng thái có nhiều đơn hàng đang ở trạng thái đó */
    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'trang_thai_id');
    }

    public function getCssClassAttribute(): string
    {
        return match (true) {
            str_contains($this->ten_trang_thai, 'Cho') || str_contains($this->ten_trang_thai, 'Chờ') => 'status-pending',
            str_contains($this->ten_trang_thai, 'lay hang') || str_contains($this->ten_trang_thai, 'Lấy hàng') || str_contains($this->ten_trang_thai, 'Dang giao') || str_contains($this->ten_trang_thai, 'Đang giao') => 'status-delivering',
            str_contains($this->ten_trang_thai, 'Da hoan') || str_contains($this->ten_trang_thai, 'Da giao') || str_contains($this->ten_trang_thai, 'Đã hoàn') || str_contains($this->ten_trang_thai, 'Đã giao') => 'status-done',
            str_contains($this->ten_trang_thai, 'Hoan') || str_contains($this->ten_trang_thai, 'Hoàn') => 'status-pending',
            str_contains($this->ten_trang_thai, 'Huy') || str_contains($this->ten_trang_thai, 'Hủy') => 'status-cancelled',
            default => 'status-pending',
        };
    }
}
