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
        Schema::create('driver_kpi_configs', function (Blueprint $table) {
            $table->id();
            $table->integer('pickup_reward')->default(0)->comment('Thưởng lấy hàng');
            $table->integer('delivery_reward')->default(0)->comment('Thưởng giao hàng');
            $table->integer('return_reward')->default(0)->comment('Thưởng hoàn hàng');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_kpi_configs');
    }
};
