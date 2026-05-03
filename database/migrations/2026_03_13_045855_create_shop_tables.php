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
        Schema::create('tai_xe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ho_ten', 100);
            $table->string('so_dien_thoai', 20)->unique();
            $table->string('bien_so_xe', 20)->nullable();
            $table->enum('trang_thai', ['Ranh', 'Dang giao', 'Tam nghi'])->default('Ranh');
            $table->timestamps();
        });

        Schema::create('khach_hang', function (Blueprint $table) {
            $table->id();
            $table->string('ten_khach', 100);
            $table->string('so_dien_thoai', 20);
            $table->text('dia_chi');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });

        Schema::create('trang_thai_don_hang', function (Blueprint $table) {
            $table->id(); // integer
            $table->string('ten_trang_thai', 50)->unique();
            $table->string('ma_mau', 20)->nullable();
            $table->integer('thu_tu')->default(0);
            $table->timestamps();
        });

        Schema::create('don_hang', function (Blueprint $table) {
            $table->id();
            $table->string('ma_don', 50)->unique();
            $table->foreignId('tai_xe_id')->nullable()->constrained('tai_xe')->onDelete('set null');
            $table->foreignId('khach_hang_id')->constrained('khach_hang')->onDelete('cascade');
            $table->foreignId('trang_thai_id')->constrained('trang_thai_don_hang'); 
            $table->decimal('tong_tien', 12, 2)->nullable();
            $table->text('ghi_chu')->nullable();
            $table->datetime('thoi_gian_giao_du_kien')->nullable();
            $table->datetime('thoi_gian_hoan_thanh')->nullable();
            $table->timestamps();
        });

        Schema::create('lich_su_trang_thai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('don_hang_id')->constrained('don_hang')->onDelete('cascade');
            $table->foreignId('trang_thai_id')->constrained('trang_thai_don_hang');
            $table->foreignId('nguoi_thay_doi')->nullable()->constrained('users');
            $table->datetime('thoi_diem')->useCurrent();
            $table->text('ghi_chu')->nullable();
        });

        Schema::create('lo_trinh', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tai_xe_id')->constrained('tai_xe')->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->datetime('thoi_gian')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lo_trinh');
        Schema::dropIfExists('lich_su_trang_thai');
        Schema::dropIfExists('don_hang');
        Schema::dropIfExists('trang_thai_don_hang');
        Schema::dropIfExists('khach_hang');
        Schema::dropIfExists('tai_xe');
    }
};
