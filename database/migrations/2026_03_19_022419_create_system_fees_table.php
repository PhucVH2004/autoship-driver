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
        Schema::create('system_fees', function (Blueprint $table) {
            $table->id();
            $table->integer('standard_delivery_fee')->default(21000);
            $table->integer('fast_delivery_fee')->default(40000);
            $table->integer('urgent_delivery_fee')->default(60000);
            
            // Tỷ lệ chia tiền cho tài xế và nền tảng (0.00 -> 1.00)
            $table->decimal('driver_ratio', 4, 3)->default(0.75);
            $table->decimal('platform_ratio', 4, 3)->default(0.25);
            
            // % Thuế tài xế và phí COD (0.00 -> 1.00)
            $table->decimal('driver_tax_percent', 4, 3)->default(0.045);
            $table->decimal('cod_fee_percent', 4, 3)->default(0.01);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_fees');
    }
};
