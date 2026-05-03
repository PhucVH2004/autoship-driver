<?php

namespace Database\Factories;

use App\Models\TrangThaiDonHang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrangThaiDonHang>
 */
class TrangThaiDonHangFactory extends Factory
{
    protected $model = TrangThaiDonHang::class;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(100, 999),
            'ten_trang_thai' => fake()->randomElement([
                'Chờ xử lý',
                'Đã lấy hàng',
                'Đang giao',
                'Đã giao',
                'Hủy',
                'Hoàn',
                'Đã hoàn',
            ]),
        ];
    }
}
