<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const SETTLEMENT_PENDING = 'pending';
    public const SETTLEMENT_CLOSED = 'closed';
    public const SETTLEMENT_TRANSFERRED = 'transferred';

    protected $table = 'transactions';

    protected $fillable = [
        'wallet_id',
        'order_id',
        'amount',
        'type',
        'reference_type',
        'settlement_status',
        'settled_at',
        'settled_by',
        'transferred_at',
        'transferred_by',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
        'settled_at' => 'datetime',
        'transferred_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function donHang()
    {
        return $this->belongsTo(DonHang::class, 'order_id');
    }

    public function settledByUser()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function transferredByUser()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
