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
        Schema::table('system_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('system_fees', 'base_weight')) {
                $table->integer('base_weight')->default(1000)->comment('Mức khối lượng gốc (gram)');
                $table->integer('base_price')->default(21000)->comment('Cước phí gốc cho mức base_weight');
                $table->integer('step_weight')->default(500)->comment('Bước khối lượng nhảy (gram)');
                $table->integer('step_price')->default(5000)->comment('Cước phí cộng thêm cho mỗi step');
                $table->decimal('zone_multiplier', 4, 2)->default(1.0)->comment('Hệ số nhân theo vùng/tuyến');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_fees', function (Blueprint $table) {
            $table->dropColumn(['base_weight', 'base_price', 'step_weight', 'step_price', 'zone_multiplier']);
        });
    }
};
