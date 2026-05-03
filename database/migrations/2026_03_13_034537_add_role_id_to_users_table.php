<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Thêm cột role_id vào bảng users
 *
 * Liên kết mỗi user với một role cụ thể.
 * Mặc định role_id = 1 (Admin) nếu không chỉ định.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm cột role_id sau cột email, có thể null trong quá trình migrate
            $table->foreignId('role_id')
                  ->nullable()
                  ->after('email')
                  ->constrained('roles')
                  ->nullOnDelete();

            // Thêm cột trạng thái nếu chưa tồn tại
            if (!Schema::hasColumn('users', 'trang_thai')) {
                $table->string('trang_thai')->default('Hoat dong')->after('role_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');

            if (Schema::hasColumn('users', 'trang_thai')) {
                $table->dropColumn('trang_thai');
            }
        });
    }
};
