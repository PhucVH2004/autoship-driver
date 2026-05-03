<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Shop;

class ShopAccountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo User tài khoản Shop
        $user = User::updateOrCreate(
            ['email' => 'shop@demo.com'],
            [
                'name'     => 'Shop Mẫu Demo',
                'email'    => 'shop@demo.com',
                'phone'    => '0909123456',
                'password' => Hash::make('password'),
                'role_id'  => 4, // Shop
            ]
        );

        // 2. Tạo thông tin Shop liên kết với User
        Shop::updateOrCreate(
            ['user_id' => $user->id],
            [
                'ten_shop'            => 'Shop Thời Trang Mẫu',
                'so_dien_thoai'       => '0909123456',
                'dia_chi'             => '123 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.HCM',
                'bank_name'           => 'Vietcombank',
                'bank_account_name'   => 'NGUYEN VAN A',
                'bank_account_number' => '1234567890',
            ]
        );

        $this->command->info('✅ Đã tạo tài khoản Shop mẫu:');
        $this->command->info('   Email   : shop@demo.com');
        $this->command->info('   Password: password');
        $this->command->info('   URL     : /shop/dashboard');
    }
}
