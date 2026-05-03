<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Thêm role Shop vào bảng roles (nếu chưa có)
        DB::table('roles')->insertOrIgnore([
            'id'         => 4,
            'name'       => 'Shop',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
