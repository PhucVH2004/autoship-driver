<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Xoá FK constraints cũ (chỉ đến bảng local) và mở rộng cột để lưu GHN IDs (numeric string).
     * GHN ProvinceID: số nguyên (vd: 202), DistrictID: số nguyên (vd: 1450), WardCode: chuỗi (vd: "550113")
     */
    public function up(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            // Xoá FK constraints
            $table->dropForeign(['tinh_thanh_id']);
            $table->dropForeign(['quan_huyen_id']);
            $table->dropForeign(['xa_phuong_id']);

            // Mở rộng cột để chứa GHN ID dạng chuỗi số
            $table->string('tinh_thanh_id', 20)->nullable()->change();
            $table->string('quan_huyen_id', 20)->nullable()->change();
            $table->string('xa_phuong_id',  20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            // Restore về varchar(10) (không thêm lại FK để tránh lỗi)
            $table->string('tinh_thanh_id', 10)->nullable()->change();
            $table->string('quan_huyen_id', 10)->nullable()->change();
            $table->string('xa_phuong_id',  10)->nullable()->change();
        });
    }
};
