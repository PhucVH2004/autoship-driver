<?php

namespace Database\Factories;

use App\Models\DonHang;
use App\Models\KhachHang;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DonHang>
 */
class DonHangFactory extends Factory
{
    protected $model = DonHang::class;

    public function definition(): array
    {
        return [
            'ma_don' => 'DH-' . fake()->unique()->numerify('####'),
            'tai_xe_id' => TaiXe::factory(),
            'khach_hang_id' => KhachHang::factory(),
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'tong_tien' => 0,
            'delivery_type' => 'standard',
            'cod_amount' => fake()->numberBetween(0, 500000),
            'cod_fee' => 0,
            'delivery_fee' => 0,
            'platform_fee' => 0,
            'driver_income' => 0,
            'driver_tax' => 0,
            'driver_real_income' => 0,
            'ghi_chu' => null,
            'thoi_gian_giao_du_kien' => now()->addHour(),
            'thoi_gian_hoan_thanh' => null,
            'delivery_photo' => null,
            'weight' => 1000,
            'length' => 20,
            'width' => 20,
            'height' => 20,
            'sender_id' => null,
            'shipping_fee' => 0,
            'total_collection' => 0,
            'da_doi_soat' => false,
        ];
    }
}
