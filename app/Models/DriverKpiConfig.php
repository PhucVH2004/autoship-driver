<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverKpiConfig extends Model
{
    protected $table = 'driver_kpi_configs';

    protected $fillable = [
        'pickup_reward',
        'delivery_reward',
        'return_reward',
    ];

    public static function current(): self
    {
        $config = self::first();
        if (!$config) {
            $config = self::create([
                'pickup_reward' => 5000,
                'delivery_reward' => 10000,
                'return_reward' => 3000,
            ]);
        }
        return $config;
    }
}
