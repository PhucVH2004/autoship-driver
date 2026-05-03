<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Shop extends Model
{
    protected $table = 'shops';

    protected $fillable = [
        'user_id',
        'ten_shop',
        'so_dien_thoai',
        'tinh_thanh_id',
        'quan_huyen_id',
        'xa_phuong_id',
        'dia_chi_cu_the',
        'latitude',
        'longitude',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
    ];

    /**
     * Accessor: Tự động ghép địa chỉ đầy đủ từ Tỉnh/Huyện/Xã.
     */
    public function getDiaChiAttribute(): string
    {
        $parts = array_filter([
            $this->dia_chi_cu_the,
            optional($this->xaPhuong)->name,
            optional($this->quanHuyen)->name,
            optional($this->tinhThanh)->name,
        ]);
        return implode(', ', $parts);
    }

    // ─── RELATIONSHIPS ──────────────────────────────────────────────────────

    public function tinhThanh(): BelongsTo
    {
        return $this->belongsTo(TinhThanh::class, 'tinh_thanh_id', 'id');
    }

    public function quanHuyen(): BelongsTo
    {
        return $this->belongsTo(QuanHuyen::class, 'quan_huyen_id', 'id');
    }

    public function xaPhuong(): BelongsTo
    {
        return $this->belongsTo(XaPhuong::class, 'xa_phuong_id', 'id');
    }

    /**
     * Shop thuộc về 1 User (tài khoản đăng nhập)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Shop có nhiều đơn hàng (với tư cách người gửi)
     */
    public function donHangs(): HasMany
    {
        return $this->hasMany(DonHang::class, 'sender_id');
    }

    /**
     * Ví đa hình — chứa số dư COD chờ đối soát
     */
    public function wallet(): MorphOne
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    // ─── HELPERS ────────────────────────────────────────────────────────────

    /**
     * Lấy hoặc tạo ví cho Shop
     */
    public function getOrCreateWallet(): Wallet
    {
        return Wallet::firstOrCreate([
            'owner_type' => self::class,
            'owner_id'   => $this->id,
        ]);
    }
}
