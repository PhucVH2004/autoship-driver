<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tạo bảng roles
 *
 * Cột name    : định danh role (dùng cho middleware) — VD: Admin, DieuPhoi, TaiXe
 * Cột mo_ta   : mô tả hiển thị — VD: "Quản trị viên toàn quyền"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // định danh cho middleware
            $table->string('mo_ta')->nullable();        // mô tả hiển thị UI
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
