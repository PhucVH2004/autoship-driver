<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('wallet_transactions_backup');
        Schema::dropIfExists('driver_wallets_backup');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('driver_wallets');
    }

    public function down(): void
    {
        // Không recreate lại bảng legacy.
    }
};
