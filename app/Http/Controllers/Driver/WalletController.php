<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\TaiXe;
use App\Models\Transaction;
use App\Models\TrangThaiDonHang;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WalletController extends Controller
{
    public function index()
    {
        $taiXe = Auth::user()->taiXe;
        if (!$taiXe) {
            return redirect()->route('driver.dashboard')->with('error', 'Tài xế không tồn tại.');
        }

        $wallet = Wallet::firstOrCreate([
            'owner_type' => TaiXe::class,
            'owner_id'   => $taiXe->id,
        ], [
            'balance'  => 0,
            'currency' => 'VND',
        ]);

        $baseTransactions = Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->with('donHang');

        $codCollectedTotal = (float) (clone $baseTransactions)
            ->where('reference_type', 'cash_collection')
            ->sum('amount');

        $kpiRewardTotal = (float) (clone $baseTransactions)
            ->where('reference_type', 'delivery_reward')
            ->sum('amount');

        $reconciledTotal = (float) (clone $baseTransactions)
            ->whereIn('reference_type', ['cod_repayment', 'cod_reconciliation'])
            ->sum('amount');

        $financialSource = $this->resolveFinancialSource();
        if ($financialSource === 'summary_view') {
            $deliveryIncomeTotal = (float) DB::table('don_hang_financial_summary')
                ->where('tai_xe_id', $taiXe->id)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->sum('driver_real_income');
        } elseif ($financialSource === 'legacy_columns') {
            $deliveryIncomeTotal = (float) DonHang::where('tai_xe_id', $taiXe->id)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->sum('driver_real_income');
        } else {
            $deliveryIncomeTotal = (float) DB::table('don_hang as dh')
                ->leftJoin('system_fees as sf', 'dh.system_fees_id', '=', 'sf.id')
                ->where('dh.tai_xe_id', $taiXe->id)
                ->where('dh.trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dh.delivery_fee, 0) * COALESCE(sf.driver_ratio, 0) * (1 - COALESCE(sf.driver_tax_percent, 0)), 2)), 0) as aggregate')
                ->value('aggregate');
        }

        $outstandingAmount = $wallet->balance < 0 ? abs((float) $wallet->balance) : 0;
        $surplusAmount = $wallet->balance > 0 ? (float) $wallet->balance : 0;

        $walletStatusLabel = match (true) {
            $wallet->balance < 0 => 'Còn phải nộp',
            $wallet->balance > 0 => 'Đang dư',
            default => 'Đã cân bằng',
        };

        $walletStatusHelp = match (true) {
            $wallet->balance < 0 => 'Bạn đang giữ tiền cần nộp lại hệ thống.',
            $wallet->balance > 0 => 'Ví đang dư sau đối soát hoặc thưởng.',
            default => 'Không còn khoản chênh lệch cần đối soát.',
        };

        $transactions = $wallet->transactions()
            ->with('donHang')
            ->latest('created_at')
            ->paginate(15)
            ->through(function ($tx) {
                $displayLabel = match (true) {
                    $tx->reference_type === 'cash_collection' => 'Thu COD',
                    $tx->reference_type === 'delivery_reward' => 'Thưởng KPI',
                    in_array($tx->reference_type, ['cod_repayment', 'cod_reconciliation'], true) => 'Đối soát COD',
                    str_contains((string) $tx->description, 'Đối soát COD') => 'Đối soát COD',
                    $tx->type === 'credit' => 'Cộng tiền',
                    default => 'Trừ tiền',
                };

                $displayTone = match (true) {
                    $displayLabel === 'Đối soát COD' => 'info',
                    $tx->type === 'credit' => 'success',
                    default => 'danger',
                };

                $tx->display_label = $displayLabel;
                $tx->display_tone = $displayTone;

                return $tx;
            });

        return view('driver.wallet.index', compact(
            'wallet',
            'transactions',
            'codCollectedTotal',
            'kpiRewardTotal',
            'reconciledTotal',
            'outstandingAmount',
            'surplusAmount',
            'deliveryIncomeTotal',
            'walletStatusLabel',
            'walletStatusHelp'
        ));
    }

    private function resolveFinancialSource(): string
    {
        if (Schema::hasTable('don_hang_financial_summary')) {
            return 'summary_view';
        }

        if (Schema::hasColumn('don_hang', 'driver_real_income')) {
            return 'legacy_columns';
        }

        return 'computed_join';
    }
}
