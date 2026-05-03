<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminSettlementSnapshot extends Model
{
    protected $table = 'admin_settlement_snapshots';

    protected $fillable = [
        'filter_type',
        'filter_value',
        'start_at',
        'end_at',
        'shop_id',
        'driver_id',
        'overview',
        'closed_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'overview' => 'array',
    ];
}
