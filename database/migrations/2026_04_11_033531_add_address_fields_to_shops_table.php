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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('tinh_thanh_id', 10)->nullable()->after('dia_chi');
            $table->string('quan_huyen_id', 10)->nullable()->after('tinh_thanh_id');
            $table->string('xa_phuong_id',  10)->nullable()->after('quan_huyen_id');
            $table->string('dia_chi_cu_the', 255)->nullable()->after('xa_phuong_id');
            $table->decimal('latitude', 10, 8)->nullable()->after('dia_chi_cu_the');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            $table->foreign('tinh_thanh_id')->references('id')->on('tinh_thanh')->nullOnDelete();
            $table->foreign('quan_huyen_id')->references('id')->on('quan_huyen')->nullOnDelete();
            $table->foreign('xa_phuong_id')->references('id')->on('xa_phuong')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropForeign(['tinh_thanh_id']);
            $table->dropForeign(['quan_huyen_id']);
            $table->dropForeign(['xa_phuong_id']);

            $table->dropColumn([
                'tinh_thanh_id', 'quan_huyen_id', 'xa_phuong_id',
                'dia_chi_cu_the', 'latitude', 'longitude'
            ]);
        });
    }
};
