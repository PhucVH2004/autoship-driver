<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

/**
 * RoleSeeder — Seed dữ liệu vai trò người dùng
 *
 * Chạy: php artisan db:seed --class=RoleSeeder
 *
 * Sử dụng firstOrCreate để tránh trùng lặp khi seed nhiều lần.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin'],     // id=1: Quản trị viên toàn quyền
            ['name' => 'DieuPhoi'],  // id=2: Điều phối viên quản lý đơn hàng
            ['name' => 'TaiXe'],     // id=3: Tài xế giao hàng
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']]);
        }

        $this->command->info('✓ Đã seed 3 roles: Admin, DieuPhoi, TaiXe');
    }
}
