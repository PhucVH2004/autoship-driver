<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tỉnh / Thành phố trực thuộc TƯ
        Schema::create('tinh_thanh', function (Blueprint $table) {
            $table->string('id', 10)->primary(); // VD: "01" = Hà Nội
            $table->string('name');
            $table->string('type', 30)->nullable(); // "Thành phố Trung ương", "Tỉnh"
        });

        // 2. Quận / Huyện
        Schema::create('quan_huyen', function (Blueprint $table) {
            $table->string('id', 10)->primary(); // VD: "001" = Ba Đình
            $table->string('name');
            $table->string('type', 30)->nullable(); // "Quận", "Huyện", "Thị xã"

            $table->string('tinh_thanh_id', 10);
            $table->foreign('tinh_thanh_id')->references('id')->on('tinh_thanh')->onDelete('cascade');
        });

        // 3. Xã / Phường / Thị trấn (Kèm toạ độ GPS mặc định)
        Schema::create('xa_phuong', function (Blueprint $table) {
            $table->string('id', 10)->primary(); // VD: "00001" = Phúc Xá
            $table->string('name');
            $table->string('type', 30)->nullable(); // "Phường", "Xã", "Thị trấn"

            // Toạ độ mặc định (trung tâm) của Xã/Phường này
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('quan_huyen_id', 10);
            $table->foreign('quan_huyen_id')->references('id')->on('quan_huyen')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xa_phuong');
        Schema::dropIfExists('quan_huyen');
        Schema::dropIfExists('tinh_thanh');
    }
};

