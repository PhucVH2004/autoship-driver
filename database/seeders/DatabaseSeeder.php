<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder — Entry point cho tất cả seeders
 *
 * Chạy: php artisan db:seed
 *
 * Thứ tự seed quan trọng:
 *   1. RoleSeeder     — tạo roles trước
 *   2. AdminUserSeeder — tạo users (cần role_id)
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,      // Step 1: Tạo 3 roles
            AdminUserSeeder::class, // Step 2: Tạo 3 tài khoản mẫu
        ]);
    }
}
