<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TinhThanh extends Model
{
    protected $table = 'tinh_thanh';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'type'];

    public function quanHuyens()
    {
        return $this->hasMany(QuanHuyen::class, 'tinh_thanh_id');
    }
}
