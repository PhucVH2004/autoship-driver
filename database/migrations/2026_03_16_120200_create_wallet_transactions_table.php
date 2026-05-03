<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('tai_xe')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('don_hang')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->string('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Tránh cộng tiền đơn hàng 2 lần (mỗi đơn chỉ có 1 giao dịch ví)
            $table->unique('order_id');
            $table->index(['driver_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

