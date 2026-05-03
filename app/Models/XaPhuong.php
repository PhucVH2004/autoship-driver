<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XaPhuong extends Model
{
    protected $table = 'xa_phuong';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'type', 'latitude', 'longitude', 'quan_huyen_id'];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function quanHuyen()
    {
        return $this->belongsTo(QuanHuyen::class, 'quan_huyen_id');
    }
}
