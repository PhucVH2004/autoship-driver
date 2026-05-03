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
            if (!Schema::hasColumn('don_hang', 'weight')) {
                $table->integer('weight')->nullable()->comment('Cân nặng (gram)');
                $table->integer('length')->nullable()->comment('Chiều dài (cm)');
                $table->integer('width')->nullable()->comment('Chiều rộng (cm)');
                $table->integer('height')->nullable()->comment('Chiều cao (cm)');
                $table->unsignedBigInteger('sender_id')->nullable()->comment('Shop/Người gửi');
                $table->decimal('shipping_fee', 12, 2)->nullable()->comment('Phí ship người gửi hoặc nhận trả');
                $table->decimal('total_collection', 12, 2)->nullable()->comment('Tổng thu = COD + Cước (nếu có)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height', 'sender_id', 'shipping_fee', 'total_collection']);
        });
    }
};
