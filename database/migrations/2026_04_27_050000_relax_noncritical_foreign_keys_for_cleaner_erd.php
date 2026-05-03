<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Giảm các FK phụ để ERD đỡ rối, vẫn giữ FK cốt lõi nghiệp vụ.
     */
    public function up(): void
    {
        $this->dropForeignKeysByColumn('route_sessions', 'tai_xe_id');
        $this->dropForeignKeysByColumn('lo_trinh', 'tai_xe_id');
        $this->dropForeignKeysByColumn('lich_su_trang_thai', 'nguoi_thay_doi');
        $this->dropForeignKeysByColumn('transactions', 'order_id');

        // FK địa chỉ hành chính (phục vụ chuẩn hóa dữ liệu, không cốt lõi dòng tiền/vận hành)
        $this->dropForeignKeysByColumn('khach_hang', 'tinh_thanh_id');
        $this->dropForeignKeysByColumn('khach_hang', 'quan_huyen_id');
        $this->dropForeignKeysByColumn('khach_hang', 'xa_phuong_id');
        $this->dropForeignKeysByColumn('shops', 'tinh_thanh_id');
        $this->dropForeignKeysByColumn('shops', 'quan_huyen_id');
        $this->dropForeignKeysByColumn('shops', 'xa_phuong_id');
    }

    /**
     * Rollback: thêm lại các FK đã nới lỏng.
     */
    public function down(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('route_sessions', 'tai_xe_id')) {
                $table->foreign('tai_xe_id', 'fk_route_sessions_tai_xe')
                    ->references('id')->on('tai_xe')
                    ->onDelete('cascade');
            }
        });

        Schema::table('lo_trinh', function (Blueprint $table) {
            if (Schema::hasColumn('lo_trinh', 'tai_xe_id')) {
                $table->foreign('tai_xe_id', 'fk_lo_trinh_tai_xe')
                    ->references('id')->on('tai_xe')
                    ->onDelete('cascade');
            }
        });

        Schema::table('lich_su_trang_thai', function (Blueprint $table) {
            if (Schema::hasColumn('lich_su_trang_thai', 'nguoi_thay_doi')) {
                $table->foreign('nguoi_thay_doi', 'fk_lich_su_nguoi_thay_doi')
                    ->references('id')->on('users')
                    ->onDelete('set null');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'order_id')) {
                $table->foreign('order_id', 'fk_transactions_order')
                    ->references('id')->on('don_hang')
                    ->onDelete('set null');
            }
        });

        Schema::table('khach_hang', function (Blueprint $table) {
            if (Schema::hasColumn('khach_hang', 'tinh_thanh_id')) {
                $table->foreign('tinh_thanh_id')
                    ->references('id')->on('tinh_thanh')
                    ->nullOnDelete();
            }
            if (Schema::hasColumn('khach_hang', 'quan_huyen_id')) {
                $table->foreign('quan_huyen_id')
                    ->references('id')->on('quan_huyen')
                    ->nullOnDelete();
            }
            if (Schema::hasColumn('khach_hang', 'xa_phuong_id')) {
                $table->foreign('xa_phuong_id')
                    ->references('id')->on('xa_phuong')
                    ->nullOnDelete();
            }
        });

        Schema::table('shops', function (Blueprint $table) {
            if (Schema::hasColumn('shops', 'tinh_thanh_id')) {
                $table->foreign('tinh_thanh_id')
                    ->references('id')->on('tinh_thanh')
                    ->nullOnDelete();
            }
            if (Schema::hasColumn('shops', 'quan_huyen_id')) {
                $table->foreign('quan_huyen_id')
                    ->references('id')->on('quan_huyen')
                    ->nullOnDelete();
            }
            if (Schema::hasColumn('shops', 'xa_phuong_id')) {
                $table->foreign('xa_phuong_id')
                    ->references('id')->on('xa_phuong')
                    ->nullOnDelete();
            }
        });
    }

    private function dropForeignKeysByColumn(string $table, string $column): void
    {
        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $column]
        );

        foreach ($constraints as $fk) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($fk) {
                $tableBlueprint->dropForeign($fk->CONSTRAINT_NAME);
            });
        }
    }
};
