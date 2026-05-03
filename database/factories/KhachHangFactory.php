<?php

namespace Database\Factories;

use App\Models\KhachHang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KhachHang>
 */
class KhachHangFactory extends Factory
{
    protected $model = KhachHang::class;

    public function definition(): array
    {
        return [
            'ten_khach' => fake()->name(),
            'so_dien_thoai' => fake()->numerify('09########'),
            'dia_chi' => fake()->address(),
            'dia_chi_cu_the' => fake()->streetAddress(),
            'tinh_thanh_id' => null,
            'quan_huyen_id' => null,
            'xa_phuong_id' => null,
            'latitude' => 10.77 + fake()->randomFloat(6, 0.001, 0.02),
            'longitude' => 106.69 + fake()->randomFloat(6, 0.001, 0.02),
        ];
    }

    public function withoutCoordinates(): static
    {
        return $this->state(fn () => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
