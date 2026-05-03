<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Thêm cột system_fees_id nếu chưa có
        if (!Schema::hasColumn('don_hang', 'system_fees_id')) {
            Schema::table('don_hang', function (Blueprint $table) {
                $table->unsignedBigInteger('system_fees_id')->default(1)->after('sender_id');
                $table->foreign('system_fees_id', 'fk_don_hang_system_fees')
                      ->references('id')->on('system_fees')
                      ->onDelete('restrict');
            });
        }

        // 2. Xóa các cột tính toán dư thừa
        $columnsToDrop = [
            'platform_fee', 'driver_income', 'driver_tax',
            'driver_real_income', 'cod_fee', 'total_collection', 'da_doi_soat',
        ];

        Schema::table('don_hang', function (Blueprint $table) use ($columnsToDrop) {
            $existing = [];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('don_hang', $col)) {
                    $existing[] = $col;
                }
            }
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });

        // 3. Tạo View tính toán tài chính tự động
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW don_hang_financial_summary AS
            SELECT
                dh.id,
                dh.ma_don,
                dh.tai_xe_id,
                dh.khach_hang_id,
                dh.trang_thai_id,
                dh.sender_id,
                dh.system_fees_id,
                dh.delivery_fee,
                dh.cod_amount,
                dh.shipping_fee,
                dh.delivery_type,
                dh.created_at,
                dh.updated_at,
                dh.thoi_gian_hoan_thanh,

                -- Các giá trị tính toán tự động từ system_fees
                sf.driver_ratio,
                sf.platform_ratio,
                sf.driver_tax_percent,
                sf.cod_fee_percent,

                ROUND(COALESCE(dh.delivery_fee, 0) * sf.platform_ratio, 2)                                AS platform_fee,
                ROUND(COALESCE(dh.delivery_fee, 0) * sf.driver_ratio, 2)                                  AS driver_income,
                ROUND(COALESCE(dh.delivery_fee, 0) * sf.driver_ratio * sf.driver_tax_percent, 2)          AS driver_tax,
                ROUND(COALESCE(dh.delivery_fee, 0) * sf.driver_ratio * (1 - sf.driver_tax_percent), 2)   AS driver_real_income,
                ROUND(COALESCE(dh.cod_amount, 0) * sf.cod_fee_percent, 2)                                 AS cod_fee,
                (COALESCE(dh.cod_amount, 0) + COALESCE(dh.shipping_fee, 0))                                AS total_collection
            FROM don_hang dh
            LEFT JOIN system_fees sf ON dh.system_fees_id = sf.id;
SQL
        );
    }

    public function down(): void
    {
        // 1. Xóa View
        DB::statement('DROP VIEW IF EXISTS don_hang_financial_summary');

        // 2. Thêm lại các cột đã xóa
        Schema::table('don_hang', function (Blueprint $table) {
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->decimal('driver_income', 10, 2)->nullable();
            $table->decimal('driver_tax', 10, 2)->nullable();
            $table->decimal('driver_real_income', 10, 2)->nullable();
            $table->decimal('cod_fee', 10, 2)->nullable();
            $table->decimal('total_collection', 10, 2)->nullable();
            $table->boolean('da_doi_soat')->default(false);
        });

        // 3. Xóa cột system_fees_id
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropForeign('fk_don_hang_system_fees');
            $table->dropColumn('system_fees_id');
        });
    }
};
