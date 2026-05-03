<?php

namespace App\Console\Commands;

use App\Models\DonHang;
use App\Models\TrangThaiDonHang;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\TaiXe;
use App\Services\DeliveryFeeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinanceRecalcCompleted extends Command
{
    protected $signature = 'finance:recalc-completed
        {--dry-run : Chỉ hiển thị, không ghi DB}
        {--chunk=200 : Số bản ghi xử lý mỗi lần}';

    protected $description = 'Tính lại tài chính cho đơn đã hoàn thành và điều chỉnh ví thống nhất.';

    public function handle(DeliveryFeeService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));

        $completedStatusId = TrangThaiDonHang::DA_GIAO;

        $this->info('Recalculate finance for completed orders (Unified Wallet System)');
        $this->line('Mode: ' . ($dryRun ? 'DRY RUN' : 'WRITE'));

        $stats = [
            'orders' => 0,
            'updated_transactions' => 0,
            'created_transactions' => 0,
            'skipped_no_driver' => 0,
            'wallet_delta' => 0.0,
        ];

        DonHang::query()
            ->where('trang_thai_id', $completedStatusId)
            ->orderBy('id')
            ->chunkById($chunk, function ($orders) use ($service, $dryRun, &$stats) {
                foreach ($orders as $order) {
                    $stats['orders']++;

                    if (!$order->tai_xe_id) {
                        $stats['skipped_no_driver']++;
                        continue;
                    }

                    $calc = $service->calculateAll(
                        (string) ($order->delivery_type ?? 'standard'),
                        (float) ($order->cod_amount ?? 0)
                    );

                    if ($dryRun) {
                        $this->line("Order {$order->id} {$order->ma_don}: new_driver_real_income={$calc['driver_real_income']}");
                        continue;
                    }

                    DB::transaction(function () use ($order, $calc, &$stats) {
                        // 1) Lock order row and update basic delivery_fee if needed
                        $lockedOrder = DonHang::whereKey($order->id)->lockForUpdate()->firstOrFail();
                        $lockedOrder->update(['delivery_fee' => $calc['delivery_fee']]);

                        $taiXe = TaiXe::find($lockedOrder->tai_xe_id);
                        if (!$taiXe) return;

                        // 2) Lock / create unified wallet
                        $wallet = Wallet::firstOrCreate([
                            'owner_type' => TaiXe::class,
                            'owner_id'   => $taiXe->id,
                        ], ['balance' => 0]);

                        $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();

                        // 3) Lock unified transaction by order_id and type
                        $tx = Transaction::where('order_id', $lockedOrder->id)
                            ->where('reference_type', 'delivery_reward') // Hoặc type credit cũ
                            ->lockForUpdate()
                            ->first();

                        $newAmount = (float) $calc['driver_real_income'];

                        if ($tx) {
                            $oldAmount = (float) $tx->amount;
                            $diff = $newAmount - $oldAmount;

                            if (abs($diff) > 0.00001) {
                                $tx->update([
                                    'amount' => $newAmount,
                                    'description' => 'Thu nhập đơn ' . ($lockedOrder->ma_don ?? ('#' . $lockedOrder->id)) . ' (recalc unified)',
                                ]);

                                $wallet->increment('balance', $diff);

                                $stats['updated_transactions']++;
                                $stats['wallet_delta'] += $diff;
                            }
                        } else {
                            Transaction::create([
                                'wallet_id'      => $wallet->id,
                                'order_id'       => $lockedOrder->id,
                                'amount'         => $newAmount,
                                'type'           => 'credit',
                                'reference_type' => 'delivery_reward',
                                'description'    => 'Thu nhập đơn ' . ($lockedOrder->ma_don ?? ('#' . $lockedOrder->id)) . ' (recalc unified)',
                                'created_at'     => now(),
                            ]);

                            $wallet->increment('balance', $newAmount);

                            $stats['created_transactions']++;
                            $stats['wallet_delta'] += $newAmount;
                        }
                    });
                }
            });

        $this->newLine();
        $this->info('Done.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['orders', $stats['orders']],
                ['skipped_no_driver', $stats['skipped_no_driver']],
                ['created_transactions', $stats['created_transactions']],
                ['updated_transactions', $stats['updated_transactions']],
                ['wallet_delta', number_format($stats['wallet_delta'], 0, ',', '.') . ' đ'],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry-run mode: no database changes were made.');
        }

        return self::SUCCESS;
    }
}

