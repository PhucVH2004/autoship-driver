<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_settlement_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('filter_type', 20);
            $table->string('filter_value', 30);
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->json('overview');
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_settlement_snapshots');
    }
};
