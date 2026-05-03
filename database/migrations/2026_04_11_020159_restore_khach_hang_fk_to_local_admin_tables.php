<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thu nhỏ cột về varchar(10) và thêm lại FK về local tables.
     * Local DB dùng ID dạng chuỗi: "01" (tỉnh), "001" (quận), "00001" (xã)
     */
    public function up(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->string('tinh_thanh_id', 10)->nullable()->change();
            $table->string('quan_huyen_id', 10)->nullable()->change();
            $table->string('xa_phuong_id',  10)->nullable()->change();

            $table->foreign('tinh_thanh_id')->references('id')->on('tinh_thanh')->nullOnDelete();
            $table->foreign('quan_huyen_id')->references('id')->on('quan_huyen')->nullOnDelete();
            $table->foreign('xa_phuong_id') ->references('id')->on('xa_phuong') ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->dropForeign(['tinh_thanh_id']);
            $table->dropForeign(['quan_huyen_id']);
            $table->dropForeign(['xa_phuong_id']);

            $table->string('tinh_thanh_id', 20)->nullable()->change();
            $table->string('quan_huyen_id', 20)->nullable()->change();
            $table->string('xa_phuong_id',  20)->nullable()->change();
        });
    }
};
