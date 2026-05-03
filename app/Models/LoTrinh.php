<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoTrinh extends Model
{
    protected $table = 'lo_trinh';

    // Bảng không có updated_at chuẩn — dùng thoi_gian thay thế
    public $timestamps = false;

    protected $fillable = [
        'tai_xe_id',
        'latitude',
        'longitude',
        'thoi_gian',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'thoi_gian' => 'datetime',
    ];

    // ──────────────────────────────────────
    // RELATIONSHIPS
    // ──────────────────────────────────────

    /** Lộ trình này thuộc về tài xế nào */
    public function taiXe()
    {
        return $this->belongsTo(TaiXe::class, 'tai_xe_id');
    }
}
