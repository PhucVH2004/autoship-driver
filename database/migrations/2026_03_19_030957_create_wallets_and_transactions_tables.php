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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->morphs('owner'); // owner_type, owner_id cho phép đa đối tượng
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency', 10)->default('VND');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('order_id')->nullable(); // Có thể tham chiếu don_hang.id
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['credit', 'debit']); // credit: cộng tiền, debit: trừ tiền
            $table->string('reference_type')->nullable(); // Tham chiếu GD (VD: pickup_reward, delivery_fee...)
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
    }
};
