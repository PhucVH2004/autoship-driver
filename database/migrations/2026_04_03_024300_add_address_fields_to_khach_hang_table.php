<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            // Lưu GHN ProvinceID / DistrictID / WardCode (số nguyên hoặc chuỗi)
            // Không dùng FK vì dữ liệu đến từ GHN API (không có trong local DB)
            $table->string('tinh_thanh_id', 20)->nullable()->after('dia_chi');
            $table->string('quan_huyen_id', 20)->nullable()->after('tinh_thanh_id');
            $table->string('xa_phuong_id',  20)->nullable()->after('quan_huyen_id');
            // Số nhà, tên đường (phần cụ thể)
            $table->string('dia_chi_cu_the', 255)->nullable()->after('xa_phuong_id');
        });
    }

    public function down(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->dropColumn(['tinh_thanh_id', 'quan_huyen_id', 'xa_phuong_id', 'dia_chi_cu_the']);
        });
    }
};
