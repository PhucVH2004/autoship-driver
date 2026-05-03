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
        Schema::table('don_hang', function (Blueprint $table) {
            // Thêm FK cho cột sender_id đã tồn tại từ migration trước
            $table->foreign('sender_id')
                  ->references('id')
                  ->on('shops')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
        });
    }
};
