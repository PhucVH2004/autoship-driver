<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaiXe extends Model
{
    use HasFactory;

    protected $table = 'tai_xe';

    protected $fillable = [
        'ho_ten',
        'so_dien_thoai',
        'bien_so_xe',
        'trang_thai',
        'current_lat',
        'current_lng',
        'last_update',
    ];

    protected $casts = [
        'last_update' => 'datetime',
    ];

    // Enum thực tế trong DB
    public const TRANG_THAI_OPTIONS = [
        'Ranh'      => 'Rảnh',
        'Dang giao' => 'Đang giao',
        'Tam nghi'  => 'Tạm nghỉ',
    ];

    public const TRANG_THAI_CLASSES = [
        'Ranh'      => 'status-done',
        'Dang giao' => 'status-delivering',
        'Tam nghi'  => 'status-cancelled',
    ];

    public function getTrangThaiLabelAttribute(): string
    {
        return self::TRANG_THAI_OPTIONS[$this->trang_thai] ?? $this->trang_thai;
    }

    public function getTrangThaiClassAttribute(): string
    {
        return self::TRANG_THAI_CLASSES[$this->trang_thai] ?? '';
    }

    /** Số đơn hàng hôm nay */
    public function getSoDonHomNayAttribute(): int
    {
        return $this->donHangs()->whereDate('created_at', today())->count();
    }

    // ──────────────────────────────────────
    // RELATIONSHIPS
    // ──────────────────────────────────────

    /** Tài xế thuộc về user nào */
    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'tai_xe_id');
    }

    /** Một tài xế có nhiều đơn hàng */
    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'tai_xe_id');
    }

    /** Một tài xế có nhiều lộ trình (vị trí GPS) */
    public function loTrinhs()
    {
        return $this->hasMany(LoTrinh::class, 'tai_xe_id');
    }

    /** Lấy vị trí GPS mới nhất của tài xế */
    public function viTriMoiNhat()
    {
        return $this->hasOne(LoTrinh::class, 'tai_xe_id')->latestOfMany('thoi_gian');
    }

    /**
     * Unified wallet for the driver (morph relation).
     */
    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    /**
     * Transactions belonging to the driver via the unified wallet.
     */
    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Wallet::class, 'owner_id', 'wallet_id')
                    ->where('wallets.owner_type', 'driver')
                    ->orderByDesc('transactions.created_at');
    }
}
