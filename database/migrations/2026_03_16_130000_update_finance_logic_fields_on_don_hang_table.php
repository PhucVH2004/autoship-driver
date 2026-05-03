<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            // Theo mô hình mới: không dùng distance/weight nữa
            if (Schema::hasColumn('don_hang', 'distance')) {
                $table->dropColumn('distance');
            }
            if (Schema::hasColumn('don_hang', 'weight')) {
                $table->dropColumn('weight');
            }

            if (!Schema::hasColumn('don_hang', 'cod_fee')) {
                $table->decimal('cod_fee', 12, 2)->default(0)->after('cod_amount');
            }
        });

        // Cập nhật enum delivery_type: standard | fast | urgent
        // Không dùng $table->enum(...)->change() để tránh phụ thuộc doctrine/dbal.
        if (Schema::hasColumn('don_hang', 'delivery_type')) {
            DB::statement("ALTER TABLE `don_hang` MODIFY `delivery_type` ENUM('standard','fast','urgent') NOT NULL DEFAULT 'standard'");

            // Nếu DB đang có giá trị cũ 'express' => map sang 'fast'
            DB::statement("UPDATE `don_hang` SET `delivery_type`='fast' WHERE `delivery_type`='express'");
        }
    }

    public function down(): void
    {
        // Rollback tối thiểu để không gây mất dữ liệu tài chính đã tính.
        Schema::table('don_hang', function (Blueprint $table) {
            if (!Schema::hasColumn('don_hang', 'distance')) {
                $table->decimal('distance', 8, 2)->nullable()->after('tong_tien');
            }
            if (!Schema::hasColumn('don_hang', 'weight')) {
                $table->unsignedInteger('weight')->nullable()->after('distance');
            }
            if (Schema::hasColumn('don_hang', 'cod_fee')) {
                $table->dropColumn('cod_fee');
            }
        });

        if (Schema::hasColumn('don_hang', 'delivery_type')) {
            DB::statement("ALTER TABLE `don_hang` MODIFY `delivery_type` ENUM('standard','express','urgent') NOT NULL DEFAULT 'standard'");
            DB::statement("UPDATE `don_hang` SET `delivery_type`='express' WHERE `delivery_type`='fast'");
        }
    }
};

