<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Xóa cột dia_chi dư thừa khỏi khach_hang
        Schema::table('khach_hang', function (Blueprint $table) {
            if (Schema::hasColumn('khach_hang', 'dia_chi')) {
                $table->dropColumn('dia_chi');
            }
        });

        // 2. Xóa cột dia_chi dư thừa khỏi shops
        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'dia_chi')) {
                $table->dropColumn('dia_chi');
            }
        });

        // 3. Tạo View địa chỉ đầy đủ cho khach_hang
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW khach_hang_full_address AS
            SELECT
                k.id,
                k.ten_khach,
                k.so_dien_thoai,
                k.latitude,
                k.longitude,
                k.tinh_thanh_id,
                tt.name AS tinh_thanh_name,
                k.quan_huyen_id,
                qh.name AS quan_huyen_name,
                k.xa_phuong_id,
                xp.name AS xa_phuong_name,
                k.dia_chi_cu_the,
                CONCAT_WS(', ',
                    NULLIF(k.dia_chi_cu_the, ''),
                    xp.name,
                    qh.name,
                    tt.name
                ) AS dia_chi_full
            FROM khach_hang k
            LEFT JOIN tinh_thanh tt ON k.tinh_thanh_id = tt.id
            LEFT JOIN quan_huyen qh ON k.quan_huyen_id = qh.id
            LEFT JOIN xa_phuong xp ON k.xa_phuong_id = xp.id;
SQL
        );

        // 4. Tạo View địa chỉ đầy đủ cho shops
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW shops_full_address AS
            SELECT
                s.id,
                s.ten_shop,
                s.so_dien_thoai,
                s.latitude,
                s.longitude,
                s.tinh_thanh_id,
                tt.name AS tinh_thanh_name,
                s.quan_huyen_id,
                qh.name AS quan_huyen_name,
                s.xa_phuong_id,
                xp.name AS xa_phuong_name,
                s.dia_chi_cu_the,
                CONCAT_WS(', ',
                    NULLIF(s.dia_chi_cu_the, ''),
                    xp.name,
                    qh.name,
                    tt.name
                ) AS dia_chi_full
            FROM shops s
            LEFT JOIN tinh_thanh tt ON s.tinh_thanh_id = tt.id
            LEFT JOIN quan_huyen qh ON s.quan_huyen_id = qh.id
            LEFT JOIN xa_phuong xp ON s.xa_phuong_id = xp.id;
SQL
        );
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS shops_full_address');
        DB::statement('DROP VIEW IF EXISTS khach_hang_full_address');

        Schema::table('khach_hang', function (Blueprint $table) {
            $table->string('dia_chi')->nullable()->after('so_dien_thoai');
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->string('dia_chi')->nullable()->after('so_dien_thoai');
        });
    }
};
