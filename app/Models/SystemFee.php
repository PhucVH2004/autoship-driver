<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemFee extends Model
{
    protected $table = 'system_fees';

    protected $fillable = [
        'standard_delivery_fee',
        'fast_delivery_fee',
        'urgent_delivery_fee',
        'driver_ratio',
        'platform_ratio',
        'driver_tax_percent',
        'cod_fee_percent',
        'base_weight',
        'base_price',
        'step_weight',
        'step_price',
        'zone_multiplier',
    ];

    /**
     * Lấy cấu hình phí hiện tại (hoặc giá trị mặc định nếu chưa có)
     */
    public static function current(): self
    {
        $fee = self::first();
        if (!$fee) {
            $fee = self::create([
                'standard_delivery_fee' => 21000,
                'fast_delivery_fee'     => 40000,
                'urgent_delivery_fee'   => 60000,
                'driver_ratio'          => 0.75,
                'platform_ratio'        => 0.25,
                'driver_tax_percent'    => 0.045,
                'cod_fee_percent'       => 0.01,
                'base_weight'           => 1000,
                'base_price'            => 21000,
                'step_weight'           => 500,
                'step_price'            => 5000,
                'zone_multiplier'       => 1.0,
            ]);
        }
        return $fee;
    }
}
