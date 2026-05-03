<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bước 1: Dọn sạch các orphaned ID trước khi thêm FK
     * Bước 2: Thêm FK constraints
     */
    public function up(): void
    {
        // =============================================
        // BƯỚC 1: DỌN SẠCH ORPHANED DATA (an toàn)
        // =============================================

        // don_hang: tai_xe_id trỏ đến tài xế không tồn tại → SET NULL
        DB::statement("
            UPDATE don_hang SET tai_xe_id = NULL
            WHERE tai_xe_id IS NOT NULL
              AND tai_xe_id NOT IN (SELECT id FROM tai_xe)
        ");

        // don_hang: sender_id trỏ đến shop không tồn tại → SET NULL
        DB::statement("
            UPDATE don_hang SET sender_id = NULL
            WHERE sender_id IS NOT NULL
              AND sender_id NOT IN (SELECT id FROM shops)
        ");

        // lich_su_trang_thai: nguoi_thay_doi trỏ đến user không tồn tại → SET NULL
        DB::statement("
            UPDATE lich_su_trang_thai SET nguoi_thay_doi = NULL
            WHERE nguoi_thay_doi IS NOT NULL
              AND nguoi_thay_doi NOT IN (SELECT id FROM users)
        ");

        // route_sessions: tai_xe_id không tồn tại → xóa record
        DB::statement("
            DELETE FROM route_sessions
            WHERE tai_xe_id NOT IN (SELECT id FROM tai_xe)
        ");

        // lo_trinh: tai_xe_id không tồn tại → xóa record
        DB::statement("
            DELETE FROM lo_trinh
            WHERE tai_xe_id NOT IN (SELECT id FROM tai_xe)
        ");

        // transactions: order_id không tồn tại → SET NULL
        DB::statement("
            UPDATE transactions SET order_id = NULL
            WHERE order_id IS NOT NULL
              AND order_id NOT IN (SELECT id FROM don_hang)
        ");

        // =============================================
        // BƯỚC 2: THÊM FOREIGN KEY CONSTRAINTS
        // =============================================

        // FK cho bảng don_hang
        Schema::table('don_hang', function (Blueprint $table) {
            if (!$this->hasFk('don_hang', 'fk_don_hang_tai_xe')) {
                $table->foreign('tai_xe_id', 'fk_don_hang_tai_xe')
                      ->references('id')->on('tai_xe')
                      ->onDelete('set null');
            }
            if (!$this->hasFk('don_hang', 'fk_don_hang_khach_hang')) {
                $table->foreign('khach_hang_id', 'fk_don_hang_khach_hang')
                      ->references('id')->on('khach_hang')
                      ->onDelete('cascade');
            }
            if (!$this->hasFk('don_hang', 'fk_don_hang_trang_thai')) {
                $table->foreign('trang_thai_id', 'fk_don_hang_trang_thai')
                      ->references('id')->on('trang_thai_don_hang')
                      ->onDelete('restrict');
            }
            if (!$this->hasFk('don_hang', 'fk_don_hang_sender')) {
                $table->foreign('sender_id', 'fk_don_hang_sender')
                      ->references('id')->on('shops')
                      ->onDelete('set null');
            }
        });

        // FK cho bảng lich_su_trang_thai
        Schema::table('lich_su_trang_thai', function (Blueprint $table) {
            if (!$this->hasFk('lich_su_trang_thai', 'fk_lich_su_don_hang')) {
                $table->foreign('don_hang_id', 'fk_lich_su_don_hang')
                      ->references('id')->on('don_hang')
                      ->onDelete('cascade');
            }
            if (!$this->hasFk('lich_su_trang_thai', 'fk_lich_su_trang_thai')) {
                $table->foreign('trang_thai_id', 'fk_lich_su_trang_thai')
                      ->references('id')->on('trang_thai_don_hang')
                      ->onDelete('restrict');
            }
            if (!$this->hasFk('lich_su_trang_thai', 'fk_lich_su_nguoi_thay_doi')) {
                $table->foreign('nguoi_thay_doi', 'fk_lich_su_nguoi_thay_doi')
                      ->references('id')->on('users')
                      ->onDelete('set null');
            }
        });

        // FK cho bảng route_sessions
        Schema::table('route_sessions', function (Blueprint $table) {
            if (!$this->hasFk('route_sessions', 'fk_route_sessions_tai_xe')) {
                $table->foreign('tai_xe_id', 'fk_route_sessions_tai_xe')
                      ->references('id')->on('tai_xe')
                      ->onDelete('cascade');
            }
        });

        // FK cho bảng lo_trinh
        Schema::table('lo_trinh', function (Blueprint $table) {
            if (!$this->hasFk('lo_trinh', 'fk_lo_trinh_tai_xe')) {
                $table->foreign('tai_xe_id', 'fk_lo_trinh_tai_xe')
                      ->references('id')->on('tai_xe')
                      ->onDelete('cascade');
            }
        });

        // FK cho bảng transactions (order_id → don_hang)
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->hasFk('transactions', 'fk_transactions_order')) {
                $table->foreign('order_id', 'fk_transactions_order')
                      ->references('id')->on('don_hang')
                      ->onDelete('set null');
            }
        });

        // =============================================
        // BƯỚC 3: THÊM INDEXES HIỆU NĂNG
        // =============================================
        Schema::table('don_hang', function (Blueprint $table) {
            $table->index('created_at', 'idx_don_hang_created_at');
            $table->index('trang_thai_id', 'idx_don_hang_trang_thai');
        });

        Schema::table('lich_su_trang_thai', function (Blueprint $table) {
            $table->index('thoi_diem', 'idx_lich_su_thoi_diem');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index('wallet_id', 'idx_transactions_wallet');
            $table->index('order_id', 'idx_transactions_order');
            $table->index('created_at', 'idx_transactions_created_at');
        });

        Schema::table('lo_trinh', function (Blueprint $table) {
            $table->index(['tai_xe_id', 'thoi_gian'], 'idx_lo_trinh_tai_xe_thoi_gian');
        });

        Schema::table('route_sessions', function (Blueprint $table) {
            $table->index(['tai_xe_id', 'route_date'], 'idx_route_sessions_tai_xe_date');
        });

        Schema::table('khach_hang', function (Blueprint $table) {
            $table->index('so_dien_thoai', 'idx_khach_hang_sdt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa indexes
        Schema::table('khach_hang', function (Blueprint $table) {
            $table->dropIndex('idx_khach_hang_sdt');
        });
        Schema::table('route_sessions', function (Blueprint $table) {
            $table->dropIndex('idx_route_sessions_tai_xe_date');
        });
        Schema::table('lo_trinh', function (Blueprint $table) {
            $table->dropIndex('idx_lo_trinh_tai_xe_thoi_gian');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_wallet');
            $table->dropIndex('idx_transactions_order');
            $table->dropIndex('idx_transactions_created_at');
        });
        Schema::table('lich_su_trang_thai', function (Blueprint $table) {
            $table->dropIndex('idx_lich_su_thoi_diem');
        });
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropIndex('idx_don_hang_created_at');
            $table->dropIndex('idx_don_hang_trang_thai');
        });

        // Xóa FK constraints
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('fk_transactions_order');
        });
        Schema::table('lo_trinh', function (Blueprint $table) {
            $table->dropForeign('fk_lo_trinh_tai_xe');
        });
        Schema::table('route_sessions', function (Blueprint $table) {
            $table->dropForeign('fk_route_sessions_tai_xe');
        });
        Schema::table('lich_su_trang_thai', function (Blueprint $table) {
            $table->dropForeign('fk_lich_su_don_hang');
            $table->dropForeign('fk_lich_su_trang_thai');
            $table->dropForeign('fk_lich_su_nguoi_thay_doi');
        });
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropForeign('fk_don_hang_tai_xe');
            $table->dropForeign('fk_don_hang_khach_hang');
            $table->dropForeign('fk_don_hang_trang_thai');
            $table->dropForeign('fk_don_hang_sender');
        });
    }

    /**
     * Kiểm tra xem FK đã tồn tại chưa để tránh duplicate constraint.
     */
    private function hasFk(string $table, string $constraintName): bool
    {
        $result = DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $constraintName]);

        return count($result) > 0;
    }
};
