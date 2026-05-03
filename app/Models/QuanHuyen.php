<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuanHuyen extends Model
{
    protected $table = 'quan_huyen';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'type', 'tinh_thanh_id'];

    public function tinhThanh()
    {
        return $this->belongsTo(TinhThanh::class, 'tinh_thanh_id');
    }

    public function xaPhuongs()
    {
        return $this->hasMany(XaPhuong::class, 'quan_huyen_id');
    }
}
