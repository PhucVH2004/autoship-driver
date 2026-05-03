<?php

namespace App\Services;

use App\Models\DonHang;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\DriverKpiConfig;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;

/**
 * FinancialService
 *
 * Xử lý dòng tiền, đối soát và KPI khi đơn hàng hoàn thành.
 */
class FinancialService
{
    /**
     * Tài xế giao thành công:
     * 1. Ghi nợ (Debit) số tiền COD + Phí ship (nếu thu tiền mặt) vào ví tài xế.
     * 2. Cộng thưởng (Credit) KPI giao hàng vào ví tài xế.
     */
    public function recordDriverTransaction(DonHang $donHang): void
    {
        $taiXe = $donHang->taiXe;
        if (!$taiXe) return;

        DB::transaction(function () use ($donHang, $taiXe) {
            $wallet = Wallet::firstOrCreate([
                'owner_type' => get_class($taiXe),
                'owner_id'   => $taiXe->id,
            ]);

            // 1. Ghi nợ tiền mặt đã thu (COD + Phí ship nếu có)
            // Giả sử total_collection là số tiền mặt tài xế cầm về
            $cashAmount = (float) ($donHang->total_collection ?? 0);
            $hasCashCollectionTx = Transaction::where('wallet_id', $wallet->id)
                ->where('order_id', $donHang->id)
                ->where('reference_type', 'cash_collection')
                ->exists();
            if ($cashAmount > 0 && !$hasCashCollectionTx) {
                $wallet->balance -= $cashAmount;
                $wallet->save();

                Transaction::create([
                    'wallet_id'      => $wallet->id,
                    'order_id'       => $donHang->id,
                    'amount'         => $cashAmount,
                    'type'           => 'debit',
                    'reference_type' => 'cash_collection',
                    'description'    => "Ghi nợ tiền mặt thu hộ đơn {$donHang->ma_don}",
                ]);
            }

            // 2. Cộng thưởng KPI giao hàng
            $kpi = DriverKpiConfig::current();
            $reward = (float) $kpi->delivery_reward;
            $hasRewardTx = Transaction::where('wallet_id', $wallet->id)
                ->where('order_id', $donHang->id)
                ->where('reference_type', 'delivery_reward')
                ->exists();
            if ($reward > 0 && !$hasRewardTx) {
                $wallet->balance += $reward;
                $wallet->save();

                Transaction::create([
                    'wallet_id'      => $wallet->id,
                    'order_id'       => $donHang->id,
                    'amount'         => $reward,
                    'type'           => 'credit',
                    'reference_type' => 'delivery_reward',
                    'description'    => "Thưởng KPI giao hàng đơn {$donHang->ma_don}",
                ]);
            }
        });
    }

    /**
     * Cộng tiền cho Shop khi giao thành công:
     * 1. Cộng (Credit) tiền COD cho Shop.
     * 2. Trừ (Debit) phí vận chuyển và phí COD vào ví Shop.
     */
    public function recordShopTransaction(DonHang $donHang): void
    {
        $shop = $donHang->shop;
        if (!$shop && $donHang->sender_id) {
            $shop = Shop::find($donHang->sender_id);
        }
        if (!$shop) return;

        DB::transaction(function () use ($donHang, $shop) {
            $wallet = Wallet::firstOrCreate([
                'owner_type' => Shop::class,
                'owner_id'   => $shop->id,
            ]);

            // 1. Cộng tiền COD
            $codAmount = (float) ($donHang->cod_amount ?? 0);
            $hasCodPaymentTx = Transaction::where('wallet_id', $wallet->id)
                ->where('order_id', $donHang->id)
                ->where('reference_type', 'cod_payment')
                ->exists();
            if ($codAmount > 0 && !$hasCodPaymentTx) {
                $wallet->balance += $codAmount;
                $wallet->save();

                Transaction::create([
                    'wallet_id'      => $wallet->id,
                    'order_id'       => $donHang->id,
                    'amount'         => $codAmount,
                    'type'           => 'credit',
                    'reference_type' => 'cod_payment',
                    'description'    => "Cộng tiền COD từ đơn {$donHang->ma_don}",
                ]);
            }

            // 2. Trừ phí vận chuyển & phí COD
            $fees = (float) ($donHang->shipping_fee ?? 0) + (float) ($donHang->cod_fee ?? 0);
            $hasServiceFeeTx = Transaction::where('wallet_id', $wallet->id)
                ->where('order_id', $donHang->id)
                ->where('reference_type', 'service_fees')
                ->exists();
            if ($fees > 0 && !$hasServiceFeeTx) {
                $wallet->balance -= $fees;
                $wallet->save();

                Transaction::create([
                    'wallet_id'      => $wallet->id,
                    'order_id'       => $donHang->id,
                    'amount'         => $fees,
                    'type'           => 'debit',
                    'reference_type' => 'service_fees',
                    'description'    => "Trừ phí vận chuyển & COD đơn {$donHang->ma_don}",
                ]);
            }
        });
    }

    /**
     * Xử lý toàn bộ tài chính khi đơn hàng hoàn thành
     */
    public function processCompletedOrder(DonHang $donHang): void
    {
        $this->recordDriverTransaction($donHang);
        $this->recordShopTransaction($donHang);
    }
}
