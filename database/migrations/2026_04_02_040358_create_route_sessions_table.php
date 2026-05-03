<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng route_sessions — Lưu lịch sử lộ trình mỗi ngày của tài xế.
 * Mỗi record = 1 phiên lộ trình (1 tài xế × 1 ngày).
 * Dùng để phân tích hiệu suất, xem lại lộ trình cũ (Giai đoạn 2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_sessions', function (Blueprint $table) {
            $table->id();

            // Tài xế thực hiện lộ trình
            $table->unsignedBigInteger('tai_xe_id');
            $table->foreign('tai_xe_id')->references('id')->on('tai_xe')->onDelete('cascade');

            // Ngày lộ trình
            $table->date('route_date');

            // Điểm xuất phát (GPS tài xế khi bắt đầu optimize)
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();

            // Thứ tự đơn hàng đã tối ưu (JSON array of don_hang.id)
            $table->json('order_sequence')->nullable();

            // Thống kê phiên
            $table->unsignedInteger('total_orders')->default(0);
            $table->unsignedInteger('completed_orders')->default(0);
            $table->unsignedInteger('failed_orders')->default(0);
            $table->decimal('total_km', 8, 2)->nullable();   // Ước tính km haversine

            // Trạng thái phiên
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');

            // Thời gian bắt đầu và kết thúc phiên
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            // 1 tài xế chỉ có 1 phiên mỗi ngày
            $table->unique(['tai_xe_id', 'route_date']);
            $table->index(['tai_xe_id', 'route_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_sessions');
    }
};

