<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            1 => 'Cho xu ly',
            2 => 'Da lay hang',
            3 => 'Dang giao',
            4 => 'Da giao',
            5 => 'Huy',
            6 => 'Hoan',
            7 => 'Da hoan',
        ];

        foreach ($statuses as $id => $name) {
            DB::table('trang_thai_don_hang')->updateOrInsert(
                ['id' => $id],
                [
                    'ten_trang_thai' => $name,
                    'thu_tu' => $id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('trang_thai_don_hang')->whereIn('id', [7])->delete();

        $statuses = [
            1 => 'Cho xu ly',
            2 => 'Da lay hang',
            3 => 'Da giao',
            4 => 'Huy',
            5 => 'Hoan',
            6 => 'Da hoan',
        ];

        foreach ($statuses as $id => $name) {
            DB::table('trang_thai_don_hang')->updateOrInsert(
                ['id' => $id],
                [
                    'ten_trang_thai' => $name,
                    'thu_tu' => $id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
};
