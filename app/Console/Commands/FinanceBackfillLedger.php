<?php

namespace App\Console\Commands;

use App\Models\DonHang;
use App\Models\TrangThaiDonHang;
use App\Services\FinancialService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinanceBackfillLedger extends Command
{
    protected $signature = 'finance:backfill-ledger
        {--dry-run : Preview only, no write}
        {--chunk=200 : Batch size}
        {--order-id= : Backfill one order by id}';

    protected $description = 'Backfill ledger transactions for completed orders (driver + shop) with idempotency.';

    public function handle(FinancialService $financialService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunk = max(1, (int) $this->option('chunk'));
        $orderId = $this->option('order-id');

        $query = DonHang::query()
            ->with(['taiXe', 'shop', 'khachHang', 'systemFee'])
            ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
            ->orderBy('id');

        if ($orderId !== null && $orderId !== '') {
            $query->whereKey((int) $orderId);
        }

        $stats = [
            'orders_scanned' => 0,
            'orders_processed' => 0,
            'orders_skipped_no_driver' => 0,
            'orders_skipped_no_shop' => 0,
            'errors' => 0,
        ];

        $this->info('Backfill ledger for completed orders');
        $this->line('Mode: ' . ($dryRun ? 'DRY RUN' : 'WRITE'));

        $query->chunkById($chunk, function ($orders) use ($dryRun, &$stats, $financialService) {
            foreach ($orders as $order) {
                $stats['orders_scanned']++;

                if (!$order->tai_xe_id) {
                    $stats['orders_skipped_no_driver']++;
                    continue;
                }

                if (!$order->sender_id) {
                    $stats['orders_skipped_no_shop']++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("Order #{$order->id} {$order->ma_don}: eligible");
                    continue;
                }

                try {
                    DB::transaction(function () use ($financialService, $order) {
                        $freshOrder = DonHang::whereKey($order->id)
                            ->lockForUpdate()
                            ->firstOrFail();

                        $financialService->processCompletedOrder($freshOrder);
                    });

                    $stats['orders_processed']++;
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $this->error("Order #{$order->id} failed: {$e->getMessage()}");
                }
            }
        });

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['orders_scanned', (string) $stats['orders_scanned']],
                ['orders_processed', (string) $stats['orders_processed']],
                ['orders_skipped_no_driver', (string) $stats['orders_skipped_no_driver']],
                ['orders_skipped_no_shop', (string) $stats['orders_skipped_no_shop']],
                ['errors', (string) $stats['errors']],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry-run mode: no database changes were made.');
        }

        return self::SUCCESS;
    }
}
