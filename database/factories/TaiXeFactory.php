<?php

namespace Database\Factories;

use App\Models\TaiXe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaiXe>
 */
class TaiXeFactory extends Factory
{
    protected $model = TaiXe::class;

    public function definition(): array
    {
        return [
            'ho_ten' => fake()->name(),
            'so_dien_thoai' => fake()->numerify('09########'),
            'bien_so_xe' => fake()->bothify('59-?# ####'),
            'trang_thai' => 'Dang giao',
            'current_lat' => 10.7769,
            'current_lng' => 106.7009,
            'last_update' => now(),
        ];
    }
}
