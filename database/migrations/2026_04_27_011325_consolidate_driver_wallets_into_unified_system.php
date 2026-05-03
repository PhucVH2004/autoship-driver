<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cập nhật bảng wallets để owner_type thành ENUM
        // Lưu ý: bảng wallets đã có sẵn owner_type là kiểu string (morphs tạo ra chuỗi)
        // Nhưng theo DB analysis nên dùng enum('driver','shop','customer')
        DB::statement("ALTER TABLE wallets MODIFY owner_type ENUM('driver', 'shop', 'customer') NOT NULL");

        // Tạo UNIQUE KEY cho wallets
        Schema::table('wallets', function (Blueprint $table) {
            // Drop morphs index cũ nếu có
            $table->dropIndex('wallets_owner_type_owner_id_index');
            $table->unique(['owner_type', 'owner_id'], 'unique_owner');
        });

        // 2. Chuyển dữ liệu từ driver_wallets -> wallets
        DB::statement("
            INSERT IGNORE INTO wallets (owner_type, owner_id, balance, currency, created_at, updated_at)
            SELECT 'driver', driver_id, balance, 'VND', created_at, updated_at
            FROM driver_wallets
            WHERE driver_id NOT IN (SELECT owner_id FROM wallets WHERE owner_type = 'driver')
        ");

        // 3. Chuyển dữ liệu từ wallet_transactions -> transactions
        DB::statement("
            INSERT IGNORE INTO transactions (wallet_id, order_id, amount, type, reference_type, description, created_at, updated_at)
            SELECT w.id, wt.order_id, wt.amount, wt.type, 'driver_kpi', wt.description, wt.created_at, wt.created_at
            FROM wallet_transactions wt
            JOIN wallets w ON wt.driver_id = w.owner_id AND w.owner_type = 'driver'
            WHERE NOT EXISTS (
                SELECT 1 FROM transactions t
                WHERE t.wallet_id = w.id AND t.order_id = wt.order_id AND t.amount = wt.amount
            )
        ");

        // 4. Đổi tên các bảng cũ thành _backup (Safety measure)
        Schema::rename('driver_wallets', 'driver_wallets_backup');
        Schema::rename('wallet_transactions', 'wallet_transactions_backup');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Phục hồi lại tên bảng cũ
        Schema::rename('driver_wallets_backup', 'driver_wallets');
        Schema::rename('wallet_transactions_backup', 'wallet_transactions');

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropUnique('unique_owner');
            $table->index(['owner_type', 'owner_id'], 'wallets_owner_type_owner_id_index');
        });

        DB::statement("ALTER TABLE wallets MODIFY owner_type VARCHAR(255) NOT NULL");
    }
};
