<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

/**
 * AdminUserSeeder — Tạo tài khoản Admin mặc định
 *
 * Chạy: php artisan db:seed --class=AdminUserSeeder
 *
 * Thông tin đăng nhập:
 *   Email    : admin@giaohang.com
 *   Password : password
 *   Role     : Admin
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy role Admin (đảm bảo RoleSeeder đã chạy trước)
        $adminRole = Role::where('name', 'Admin')->first();

        if (!$adminRole) {
            $this->command->error('❌ Role Admin chưa được tạo. Hãy chạy RoleSeeder trước.');
            return;
        }

        // Tạo tài khoản Admin nếu chưa tồn tại
        $admin = User::firstOrCreate(
            ['email' => 'admin@giaohang.com'],
            [
                'name'       => 'Quản Trị Viên',
                'password'   => Hash::make('password'),
                'role_id'    => $adminRole->id,
                'trang_thai' => 'Hoat dong',
            ]
        );

        // Tạo tài khoản DieuPhoi mẫu
        $dieuPhoiRole = Role::where('name', 'DieuPhoi')->first();
        if ($dieuPhoiRole) {
            User::firstOrCreate(
                ['email' => 'dieupho@giaohang.com'],
                [
                    'name'       => 'Điều Phối Viên',
                    'password'   => Hash::make('password'),
                    'role_id'    => $dieuPhoiRole->id,
                    'trang_thai' => 'Hoat dong',
                ]
            );
        }

        // Tạo tài khoản TaiXe mẫu
        $taiXeRole = Role::where('name', 'TaiXe')->first();
        if ($taiXeRole) {
            User::firstOrCreate(
                ['email' => 'taixe@giaohang.com'],
                [
                    'name'       => 'Tài Xế Mẫu',
                    'password'   => Hash::make('password'),
                    'role_id'    => $taiXeRole->id,
                    'trang_thai' => 'Hoat dong',
                ]
            );
        }

        $this->command->info('✓ Đã tạo tài khoản Admin, DieuPhoi, TaiXe mẫu');
        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['admin@giaohang.com',   'password', 'Admin'],
                ['dieupho@giaohang.com', 'password', 'DieuPhoi'],
                ['taixe@giaohang.com',   'password', 'TaiXe'],
            ]
        );
    }
}
