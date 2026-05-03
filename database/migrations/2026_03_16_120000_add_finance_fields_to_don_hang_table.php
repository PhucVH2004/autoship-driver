<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            // Thông số đầu vào để tính phí
            $table->unsignedInteger('weight')->nullable()->after('tong_tien'); // gram
            $table->decimal('distance', 8, 2)->nullable()->after('weight'); // km
            $table->enum('delivery_type', ['standard', 'express', 'urgent'])->default('standard')->after('distance');
            $table->decimal('cod_amount', 12, 2)->default(0)->after('delivery_type');

            // Kết quả tài chính (tự tính khi đơn hoàn thành)
            $table->decimal('delivery_fee', 12, 2)->nullable()->after('cod_amount');
            $table->decimal('platform_fee', 12, 2)->nullable()->after('delivery_fee');
            $table->decimal('driver_income', 12, 2)->nullable()->after('platform_fee');
            $table->decimal('driver_tax', 12, 2)->nullable()->after('driver_income');
            $table->decimal('driver_real_income', 12, 2)->nullable()->after('driver_tax');
        });
    }

    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropColumn([
                'weight',
                'distance',
                'delivery_type',
                'cod_amount',
                'delivery_fee',
                'platform_fee',
                'driver_income',
                'driver_tax',
                'driver_real_income',
            ]);
        });
    }
};

