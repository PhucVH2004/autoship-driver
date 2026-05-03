<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KhachHang extends Model
{
    use HasFactory;

    protected $table = 'khach_hang';

    protected $fillable = [
        'ten_khach',
        'so_dien_thoai',
        'dia_chi_cu_the',
        'tinh_thanh_id',
        'quan_huyen_id',
        'xa_phuong_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    /**
     * Accessor: Tự động ghép địa chỉ đầy đủ từ Tỉnh/Huyện/Xã (thay thế cột dia_chi cũ).
     * Vẫn trả về giá trị cho code cũ dùng $khachHang->dia_chi
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

    // ──────────────────────────────────────
    // RELATIONSHIPS
    // ──────────────────────────────────────

    public function tinhThanh()
    {
        return $this->belongsTo(TinhThanh::class, 'tinh_thanh_id', 'id');
    }

    public function quanHuyen()
    {
        return $this->belongsTo(QuanHuyen::class, 'quan_huyen_id', 'id');
    }

    public function xaPhuong()
    {
        return $this->belongsTo(XaPhuong::class, 'xa_phuong_id', 'id');
    }

    /** Một khách hàng có nhiều đơn hàng */
    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'khach_hang_id');
    }
}
