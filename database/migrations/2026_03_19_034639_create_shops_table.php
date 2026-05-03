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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Tài khoản đăng nhập của Shop');
            $table->string('ten_shop');
            $table->string('so_dien_thoai', 20)->nullable();
            $table->text('dia_chi')->nullable();
            $table->string('bank_name', 100)->nullable()->comment('Tên ngân hàng');
            $table->string('bank_account_name', 150)->nullable()->comment('Tên chủ tài khoản');
            $table->string('bank_account_number', 50)->nullable()->comment('Số tài khoản ngân hàng');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
