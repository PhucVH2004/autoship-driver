<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSuTrangThai extends Model
{
    protected $table = 'lich_su_trang_thai';

    // Bảng này không có updated_at, chỉ có thoi_diem
    public $timestamps = false;

    protected $fillable = [
        'don_hang_id',
        'trang_thai_id',
        'nguoi_thay_doi',  // user_id của người thực hiện thay đổi
        'thoi_diem',
        'ghi_chu',
    ];

    protected $casts = [
        'thoi_diem' => 'datetime',
    ];

    // ──────────────────────────────────────
    // RELATIONSHIPS
    // ──────────────────────────────────────

    /** Lịch sử này thuộc về đơn hàng nào */
    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'don_hang_id');
    }

    /** Trạng thái tương ứng */
    public function trangThai()
    {
        return $this->belongsTo(TrangThaiDonHang::class, 'trang_thai_id');
    }

    /** Người đã thực hiện thay đổi (User) */
    public function nguoiThayDoi()
    {
        return $this->belongsTo(\App\Models\User::class, 'nguoi_thay_doi');
    }
}
