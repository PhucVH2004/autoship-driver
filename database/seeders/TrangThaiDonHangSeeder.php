<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrangThaiDonHangSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'ten_trang_thai' => 'Chờ xử lý', 'ma_mau' => '#FFA500', 'thu_tu' => 1],
            ['id' => 2, 'ten_trang_thai' => 'Đã lấy hàng', 'ma_mau' => '#3182CE', 'thu_tu' => 2],
            ['id' => 3, 'ten_trang_thai' => 'Đang giao', 'ma_mau' => '#4F8EF7', 'thu_tu' => 3],
            ['id' => 4, 'ten_trang_thai' => 'Đã giao', 'ma_mau' => '#22d3a0', 'thu_tu' => 4],
            ['id' => 5, 'ten_trang_thai' => 'Hủy', 'ma_mau' => '#F56565', 'thu_tu' => 5],
            ['id' => 6, 'ten_trang_thai' => 'Hoàn hàng', 'ma_mau' => '#F6AD55', 'thu_tu' => 6],
            ['id' => 7, 'ten_trang_thai' => 'Đã hoàn', 'ma_mau' => '#805AD5', 'thu_tu' => 7],
        ];

        foreach ($statuses as $status) {
            DB::table('trang_thai_don_hang')->updateOrInsert(
                ['id' => $status['id']],
                [
                    'ten_trang_thai' => $status['ten_trang_thai'],
                    'ma_mau' => $status['ma_mau'],
                    'thu_tu' => $status['thu_tu'],
                ]
            );
        }
    }
}
