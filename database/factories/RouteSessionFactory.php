<?php

namespace Database\Factories;

use App\Models\RouteSession;
use App\Models\TaiXe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouteSession>
 */
class RouteSessionFactory extends Factory
{
    protected $model = RouteSession::class;

    public function definition(): array
    {
        return [
            'tai_xe_id' => TaiXe::factory(),
            'route_date' => today(),
            'start_lat' => 10.7769,
            'start_lng' => 106.7009,
            'order_sequence' => [],
            'total_orders' => 0,
            'completed_orders' => 0,
            'failed_orders' => 0,
            'total_km' => 0,
            'status' => 'active',
            'started_at' => now(),
            'finished_at' => null,
        ];
    }
}
