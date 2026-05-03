<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'balance',
        'currency',
    ];

    protected $casts = [
        'balance' => 'float',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }
}
