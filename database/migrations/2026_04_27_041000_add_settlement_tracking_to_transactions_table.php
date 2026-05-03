<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'settlement_status')) {
                $table->string('settlement_status', 20)->default('pending')->after('reference_type');
                $table->index('settlement_status', 'idx_transactions_settlement_status');
            }
            if (!Schema::hasColumn('transactions', 'settled_at')) {
                $table->dateTime('settled_at')->nullable()->after('settlement_status');
            }
            if (!Schema::hasColumn('transactions', 'settled_by')) {
                $table->unsignedBigInteger('settled_by')->nullable()->after('settled_at');
                $table->index('settled_by', 'idx_transactions_settled_by');
            }
            if (!Schema::hasColumn('transactions', 'transferred_at')) {
                $table->dateTime('transferred_at')->nullable()->after('settled_by');
            }
            if (!Schema::hasColumn('transactions', 'transferred_by')) {
                $table->unsignedBigInteger('transferred_by')->nullable()->after('transferred_at');
                $table->index('transferred_by', 'idx_transactions_transferred_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'transferred_by')) {
                $table->dropIndex('idx_transactions_transferred_by');
                $table->dropColumn('transferred_by');
            }
            if (Schema::hasColumn('transactions', 'transferred_at')) {
                $table->dropColumn('transferred_at');
            }
            if (Schema::hasColumn('transactions', 'settled_by')) {
                $table->dropIndex('idx_transactions_settled_by');
                $table->dropColumn('settled_by');
            }
            if (Schema::hasColumn('transactions', 'settled_at')) {
                $table->dropColumn('settled_at');
            }
            if (Schema::hasColumn('transactions', 'settlement_status')) {
                $table->dropIndex('idx_transactions_settlement_status');
                $table->dropColumn('settlement_status');
            }
        });
    }
};
