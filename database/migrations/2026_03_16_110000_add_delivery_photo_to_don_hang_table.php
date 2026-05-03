<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột delivery_photo vào bảng don_hang
     * để tài xế upload ảnh xác nhận giao hàng (Yêu cầu 7)
     */
    public function up(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->string('delivery_photo')->nullable()->after('thoi_gian_hoan_thanh')
                  ->comment('Đường dẫn ảnh xác nhận giao hàng');
        });
    }

    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropColumn('delivery_photo');
        });
    }
};
