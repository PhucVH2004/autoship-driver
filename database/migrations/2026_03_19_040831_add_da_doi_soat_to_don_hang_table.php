<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->boolean('da_doi_soat')
                  ->default(false)
                  ->after('total_collection')
                  ->comment('Đã đối soát COD cho Shop chưa?');
        });
    }

    public function down(): void
    {
        Schema::table('don_hang', function (Blueprint $table) {
            $table->dropColumn('da_doi_soat');
        });
    }
};
