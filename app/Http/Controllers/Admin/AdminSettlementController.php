<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSettlementSnapshot;
use App\Models\Shop;
use App\Models\TaiXe;
use App\Models\Transaction;
use App\Models\TrangThaiDonHang;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminSettlementController extends Controller
{
    public function index(Request $request)
    {
        [$filterType, $filterValue, $startAt, $endAt] = $this->resolveFilterWindow($request);
        $selectedShopId = $request->filled('shop_id') ? (int) $request->input('shop_id') : null;
        $selectedDriverId = $request->filled('driver_id') ? (int) $request->input('driver_id') : null;

        $hasSystemFeesId = Schema::hasColumn('don_hang', 'system_fees_id');
        $hasPlatformFee = Schema::hasColumn('don_hang', 'platform_fee');
        $hasCodFee = Schema::hasColumn('don_hang', 'cod_fee');
        $hasDriverRealIncome = Schema::hasColumn('don_hang', 'driver_real_income');

        $platformFeeExpr = $hasSystemFeesId
            ? 'ROUND(COALESCE(dh.delivery_fee, 0) * COALESCE(sf.platform_ratio, 0), 2)'
            : ($hasPlatformFee ? 'COALESCE(dh.platform_fee, 0)' : '0');
        $codFeeExpr = $hasSystemFeesId
            ? 'ROUND(COALESCE(dh.cod_amount, 0) * COALESCE(sf.cod_fee_percent, 0), 2)'
            : ($hasCodFee ? 'COALESCE(dh.cod_fee, 0)' : '0');
        $driverIncomeExpr = $hasSystemFeesId
            ? 'ROUND(COALESCE(dh.delivery_fee, 0) * COALESCE(sf.driver_ratio, 0) * (1 - COALESCE(sf.driver_tax_percent, 0)), 2)'
            : ($hasDriverRealIncome ? 'COALESCE(dh.driver_real_income, 0)' : '0');

        $completedStatusId = TrangThaiDonHang::DA_GIAO;
        $completedOrdersQuery = DB::table('don_hang as dh');
        if ($hasSystemFeesId) {
            $completedOrdersQuery->leftJoin('system_fees as sf', 'dh.system_fees_id', '=', 'sf.id');
        }
        $completedOrdersQuery
            ->where('dh.trang_thai_id', $completedStatusId)
            ->whereBetween(DB::raw('COALESCE(dh.thoi_gian_hoan_thanh, dh.updated_at)'), [$startAt, $endAt]);
        if ($selectedShopId) {
            $completedOrdersQuery->where('dh.sender_id', $selectedShopId);
        }
        if ($selectedDriverId) {
            $completedOrdersQuery->where('dh.tai_xe_id', $selectedDriverId);
        }

        $overview = (array) (clone $completedOrdersQuery)
            ->selectRaw('COUNT(*) as total_completed_orders')
            ->selectRaw('COALESCE(SUM(COALESCE(dh.cod_amount, 0)), 0) as total_cod')
            ->selectRaw('COALESCE(SUM(COALESCE(dh.shipping_fee, 0)), 0) as total_shipping_fee')
            ->selectRaw("COALESCE(SUM($platformFeeExpr), 0) as total_platform_fee")
            ->selectRaw("COALESCE(SUM($codFeeExpr), 0) as total_cod_fee")
            ->selectRaw("COALESCE(SUM($driverIncomeExpr), 0) as total_driver_income")
            ->first();

        $overview['total_shop_net'] = (float) $overview['total_cod']
            - (float) $overview['total_shipping_fee']
            - (float) $overview['total_cod_fee'];
        $overview['total_admin_revenue'] = (float) $overview['total_platform_fee']
            + (float) $overview['total_cod_fee'];

        $shopStats = (clone $completedOrdersQuery)
            ->leftJoin('shops as s', 'dh.sender_id', '=', 's.id')
            ->selectRaw('s.id as shop_id')
            ->selectRaw('COALESCE(s.ten_shop, CONCAT("Shop #", dh.sender_id)) as shop_name')
            ->selectRaw('COUNT(*) as completed_orders')
            ->selectRaw('COALESCE(SUM(COALESCE(dh.cod_amount, 0)), 0) as cod_total')
            ->selectRaw('COALESCE(SUM(COALESCE(dh.shipping_fee, 0)), 0) as shipping_total')
            ->selectRaw("COALESCE(SUM($codFeeExpr), 0) as cod_fee_total")
            ->selectRaw("COALESCE(SUM(COALESCE(dh.cod_amount, 0) - COALESCE(dh.shipping_fee, 0) - ($codFeeExpr)), 0) as shop_net")
            ->whereNotNull('dh.sender_id')
            ->groupBy('s.id', 's.ten_shop', 'dh.sender_id')
            ->orderByDesc('shop_net')
            ->limit(100)
            ->get();

        $driverStats = (clone $completedOrdersQuery)
            ->leftJoin('tai_xe as tx', 'dh.tai_xe_id', '=', 'tx.id')
            ->selectRaw('tx.id as driver_id')
            ->selectRaw('COALESCE(tx.ho_ten, CONCAT("Tài xế #", dh.tai_xe_id)) as driver_name')
            ->selectRaw('COUNT(*) as completed_orders')
            ->selectRaw('COALESCE(SUM(COALESCE(dh.delivery_fee, 0)), 0) as delivery_fee_total')
            ->selectRaw("COALESCE(SUM($driverIncomeExpr), 0) as driver_income_total")
            ->selectRaw('COALESCE(SUM(COALESCE(dh.cod_amount, 0) + COALESCE(dh.shipping_fee, 0)), 0) as cash_holding_total')
            ->whereNotNull('dh.tai_xe_id')
            ->groupBy('tx.id', 'tx.ho_ten', 'dh.tai_xe_id')
            ->orderByDesc('driver_income_total')
            ->limit(100)
            ->get();

        $walletSummary = $this->walletSummary();
        $shops = Shop::query()->select('id', 'ten_shop')->orderBy('ten_shop')->get();
        $drivers = TaiXe::query()->select('id', 'ho_ten')->orderBy('ho_ten')->get();
        $settlementQueue = $this->buildSettlementQueue(
            $startAt,
            $endAt,
            $selectedShopId,
            $selectedDriverId,
            $codFeeExpr
        );
        $recentSnapshots = AdminSettlementSnapshot::query()
            ->latest('id')
            ->limit(10)
            ->get();

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($filterType, $filterValue, $startAt, $endAt, $overview, $shopStats, $driverStats);
        }

        return view('admin.settlement.index', compact(
            'filterType',
            'filterValue',
            'startAt',
            'endAt',
            'selectedShopId',
            'selectedDriverId',
            'overview',
            'shopStats',
            'driverStats',
            'walletSummary',
            'shops',
            'drivers',
            'settlementQueue',
            'recentSnapshots'
        ));
    }

    public function close(Request $request)
    {
        [$filterType, $filterValue, $startAt, $endAt] = $this->resolveFilterWindow($request);
        $selectedShopId = $request->filled('shop_id') ? (int) $request->input('shop_id') : null;
        $selectedDriverId = $request->filled('driver_id') ? (int) $request->input('driver_id') : null;
        $orderId = $request->filled('order_id') ? (int) $request->input('order_id') : null;

        $overview = [
            'total_completed_orders' => (float) $request->input('total_completed_orders', 0),
            'total_cod' => (float) $request->input('total_cod', 0),
            'total_shipping_fee' => (float) $request->input('total_shipping_fee', 0),
            'total_platform_fee' => (float) $request->input('total_platform_fee', 0),
            'total_cod_fee' => (float) $request->input('total_cod_fee', 0),
            'total_driver_income' => (float) $request->input('total_driver_income', 0),
            'total_shop_net' => (float) $request->input('total_shop_net', 0),
            'total_admin_revenue' => (float) $request->input('total_admin_revenue', 0),
        ];

        AdminSettlementSnapshot::create([
            'filter_type' => $filterType,
            'filter_value' => $filterValue,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'shop_id' => $selectedShopId,
            'driver_id' => $selectedDriverId,
            'overview' => $overview,
            'closed_by' => auth()->id(),
        ]);

        $this->updateSettlementStatusByFilter(
            $startAt,
            $endAt,
            $selectedShopId,
            $selectedDriverId,
            Transaction::SETTLEMENT_CLOSED,
            $orderId
        );

        return redirect()
            ->route('admin.settlement.index', $request->only(['filter_type', 'date', 'month', 'year', 'shop_id', 'driver_id']))
            ->with('success', 'Đã chốt kỳ đối soát thành công.');
    }

    public function transfer(Request $request)
    {
        [$filterType, $filterValue, $startAt, $endAt] = $this->resolveFilterWindow($request);
        $selectedShopId = $request->filled('shop_id') ? (int) $request->input('shop_id') : null;
        $selectedDriverId = $request->filled('driver_id') ? (int) $request->input('driver_id') : null;
        $orderId = $request->filled('order_id') ? (int) $request->input('order_id') : null;

        $updated = $this->updateSettlementStatusByFilter(
            $startAt,
            $endAt,
            $selectedShopId,
            $selectedDriverId,
            Transaction::SETTLEMENT_TRANSFERRED,
            $orderId
        );

        return redirect()
            ->route('admin.settlement.index', [
                'filter_type' => $filterType,
                'date' => $filterType === 'day' ? $filterValue : null,
                'month' => $filterType === 'month' ? $filterValue : null,
                'year' => $filterType === 'year' ? $filterValue : null,
                'shop_id' => $selectedShopId,
                'driver_id' => $selectedDriverId,
            ])
            ->with('success', "Đã đánh dấu chuyển khoản cho {$updated} giao dịch.");
    }

    public function confirmDriverRepayment(Request $request)
    {
        [$filterType, $filterValue, $startAt, $endAt] = $this->resolveFilterWindow($request);
        $selectedShopId = $request->filled('shop_id') ? (int) $request->input('shop_id') : null;
        $selectedDriverId = $request->filled('driver_id') ? (int) $request->input('driver_id') : null;
        $orderId = $request->filled('order_id') ? (int) $request->input('order_id') : null;

        $ordersQuery = DB::table('don_hang')
            ->select('id', 'ma_don', 'tai_xe_id')
            ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
            ->whereNotNull('tai_xe_id')
            ->whereBetween(DB::raw('COALESCE(thoi_gian_hoan_thanh, updated_at)'), [$startAt, $endAt]);

        if ($selectedShopId) {
            $ordersQuery->where('sender_id', $selectedShopId);
        }
        if ($selectedDriverId) {
            $ordersQuery->where('tai_xe_id', $selectedDriverId);
        }
        if ($orderId) {
            $ordersQuery->where('id', $orderId);
        }

        $orders = $ordersQuery->get();
        $createdTransactions = 0;
        $totalRepaidAmount = 0.0;

        DB::transaction(function () use ($orders, &$createdTransactions, &$totalRepaidAmount) {
            foreach ($orders as $order) {
                $wallet = Wallet::firstOrCreate([
                    'owner_type' => TaiXe::class,
                    'owner_id' => $order->tai_xe_id,
                ], [
                    'balance' => 0,
                    'currency' => 'VND',
                ]);

                $cashCollected = (float) Transaction::query()
                    ->where('wallet_id', $wallet->id)
                    ->where('order_id', $order->id)
                    ->where('reference_type', 'cash_collection')
                    ->sum('amount');

                $alreadyRepaid = (float) Transaction::query()
                    ->where('wallet_id', $wallet->id)
                    ->where('order_id', $order->id)
                    ->whereIn('reference_type', ['cod_repayment', 'cod_reconciliation'])
                    ->sum('amount');

                $outstanding = round($cashCollected - $alreadyRepaid, 2);
                if ($outstanding <= 0) {
                    continue;
                }

                $wallet->increment('balance', $outstanding);

                Transaction::create([
                    'wallet_id' => $wallet->id,
                    'order_id' => $order->id,
                    'amount' => $outstanding,
                    'type' => 'credit',
                    'reference_type' => 'cod_repayment',
                    'settlement_status' => Transaction::SETTLEMENT_CLOSED,
                    'settled_at' => now(),
                    'settled_by' => auth()->id(),
                    'description' => "Admin xác nhận tài xế nộp tiền đơn {$order->ma_don}",
                ]);

                $createdTransactions++;
                $totalRepaidAmount += $outstanding;
            }
        });

        return redirect()
            ->route('admin.settlement.index', [
                'filter_type' => $filterType,
                'date' => $filterType === 'day' ? $filterValue : null,
                'month' => $filterType === 'month' ? $filterValue : null,
                'year' => $filterType === 'year' ? $filterValue : null,
                'shop_id' => $selectedShopId,
                'driver_id' => $selectedDriverId,
            ])
            ->with('success', "Đã xác nhận nộp tiền cho {$createdTransactions} đơn, tổng " . number_format($totalRepaidAmount, 0, ',', '.') . ' đ.');
    }

    private function resolveFilterWindow(Request $request): array
    {
        $type = (string) $request->input('filter_type', 'day');
        if (!in_array($type, ['day', 'month', 'year'], true)) {
            $type = 'day';
        }

        if ($type === 'month') {
            $value = (string) $request->input('month', '');
            if (!preg_match('/^\d{4}-\d{2}$/', $value)) {
                $value = now()->format('Y-m');
            }
            $baseDate = Carbon::createFromFormat('Y-m-d', $value . '-01');
            $start = $baseDate->copy()->startOfMonth();
            $end = $baseDate->copy()->endOfMonth();
            return [$type, $value, $start, $end];
        }

        if ($type === 'year') {
            $value = (int) $request->input('year', 0);
            if ($value < 2000 || $value > ((int) now()->format('Y') + 1)) {
                $value = (int) now()->format('Y');
            }
            $start = now()->setDate($value, 1, 1)->startOfDay();
            $end = now()->setDate($value, 12, 31)->endOfDay();
            return [$type, (string) $value, $start, $end];
        }

        $value = (string) $request->input('date', '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $value = today()->format('Y-m-d');
        }
        $baseDate = Carbon::createFromFormat('Y-m-d', $value);
        $start = $baseDate->copy()->startOfDay();
        $end = $baseDate->copy()->endOfDay();

        return ['day', $value, $start, $end];
    }

    private function walletSummary(): array
    {
        $shopOwnerType = Shop::class;
        $driverOwnerType = TaiXe::class;

        $shopPendingSettlement = (float) DB::table('wallets')
            ->where('owner_type', $shopOwnerType)
            ->where('balance', '>', 0)
            ->sum('balance');

        $driverOutstanding = (float) DB::table('wallets')
            ->where('owner_type', $driverOwnerType)
            ->where('balance', '<', 0)
            ->selectRaw('COALESCE(SUM(ABS(balance)), 0) as aggregate')
            ->value('aggregate');

        $driverSurplus = (float) DB::table('wallets')
            ->where('owner_type', $driverOwnerType)
            ->where('balance', '>', 0)
            ->sum('balance');

        return [
            'shop_pending_settlement' => $shopPendingSettlement,
            'driver_outstanding' => $driverOutstanding,
            'driver_surplus' => $driverSurplus,
        ];
    }

    private function exportCsv(
        string $filterType,
        string $filterValue,
        Carbon $startAt,
        Carbon $endAt,
        array $overview,
        \Illuminate\Support\Collection $shopStats,
        \Illuminate\Support\Collection $driverStats
    ): StreamedResponse {
        $filename = 'doi-soat-' . $filterType . '-' . $filterValue . '.csv';

        return response()->streamDownload(function () use ($filterType, $filterValue, $startAt, $endAt, $overview, $shopStats, $driverStats) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($out, ['BAO CAO DOI SOAT']);
            fputcsv($out, ['Kieu loc', $filterType]);
            fputcsv($out, ['Gia tri', $filterValue]);
            fputcsv($out, ['Tu', $startAt->format('Y-m-d H:i:s')]);
            fputcsv($out, ['Den', $endAt->format('Y-m-d H:i:s')]);
            fputcsv($out, []);
            fputcsv($out, ['Tong quan']);
            fputcsv($out, ['Tong don hoan thanh', $overview['total_completed_orders'] ?? 0]);
            fputcsv($out, ['Shop thuc nhan', $overview['total_shop_net'] ?? 0]);
            fputcsv($out, ['Tai xe thuc nhan', $overview['total_driver_income'] ?? 0]);
            fputcsv($out, ['Doanh thu admin', $overview['total_admin_revenue'] ?? 0]);
            fputcsv($out, []);

            fputcsv($out, ['Doanh thu tung shop']);
            fputcsv($out, ['Shop', 'Don HT', 'COD', 'Phi ship', 'Phi COD', 'Thuc nhan']);
            foreach ($shopStats as $row) {
                fputcsv($out, [
                    $row->shop_name,
                    (float) $row->completed_orders,
                    (float) $row->cod_total,
                    (float) $row->shipping_total,
                    (float) $row->cod_fee_total,
                    (float) $row->shop_net,
                ]);
            }
            fputcsv($out, []);

            fputcsv($out, ['Doanh thu tung tai xe']);
            fputcsv($out, ['Tai xe', 'Don HT', 'Phi giao', 'Thuc nhan', 'Giu tien mat']);
            foreach ($driverStats as $row) {
                fputcsv($out, [
                    $row->driver_name,
                    (float) $row->completed_orders,
                    (float) $row->delivery_fee_total,
                    (float) $row->driver_income_total,
                    (float) $row->cash_holding_total,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function updateSettlementStatusByFilter(
        Carbon $startAt,
        Carbon $endAt,
        ?int $shopId,
        ?int $driverId,
        string $targetStatus,
        ?int $orderId = null
    ): int {
        $shopWalletQuery = DB::table('wallets')
            ->select('wallets.id')
            ->where('wallets.owner_type', Shop::class);
        if ($shopId) {
            $shopWalletQuery->where('wallets.owner_id', $shopId);
        }

        $orderQuery = DB::table('don_hang')
            ->select('id')
            ->whereBetween(DB::raw('COALESCE(thoi_gian_hoan_thanh, updated_at)'), [$startAt, $endAt]);
        if ($shopId) {
            $orderQuery->where('sender_id', $shopId);
        }
        if ($driverId) {
            $orderQuery->where('tai_xe_id', $driverId);
        }
        if ($orderId) {
            $orderQuery->where('id', $orderId);
        }

        $txQuery = Transaction::query()
            ->whereIn('wallet_id', $shopWalletQuery)
            ->whereIn('order_id', $orderQuery)
            ->whereIn('reference_type', ['cod_payment', 'service_fees']);

        if ($targetStatus === Transaction::SETTLEMENT_CLOSED) {
            return $txQuery->where(function ($query) {
                $query->where('settlement_status', Transaction::SETTLEMENT_PENDING)
                    ->orWhereNull('settlement_status');
            })
                ->update([
                    'settlement_status' => Transaction::SETTLEMENT_CLOSED,
                    'settled_at' => now(),
                    'settled_by' => auth()->id(),
                ]);
        }

        return $txQuery
            ->where(function ($query) {
                $query->whereIn('settlement_status', [Transaction::SETTLEMENT_PENDING, Transaction::SETTLEMENT_CLOSED])
                    ->orWhereNull('settlement_status');
            })
            ->update([
                'settlement_status' => Transaction::SETTLEMENT_TRANSFERRED,
                'transferred_at' => now(),
                'transferred_by' => auth()->id(),
                'settled_at' => DB::raw('COALESCE(settled_at, NOW())'),
                'settled_by' => DB::raw('COALESCE(settled_by, ' . ((int) auth()->id()) . ')'),
            ]);
    }

    private function buildSettlementQueue(
        Carbon $startAt,
        Carbon $endAt,
        ?int $shopId,
        ?int $driverId,
        string $codFeeExpr
    ): \Illuminate\Support\Collection {
        $cashCollectionSub = DB::table('transactions as t')
            ->join('wallets as w', 't.wallet_id', '=', 'w.id')
            ->selectRaw('t.order_id, COALESCE(SUM(t.amount), 0) as cash_collected')
            ->where('w.owner_type', TaiXe::class)
            ->where('t.reference_type', 'cash_collection')
            ->groupBy('t.order_id');

        $driverRepaymentSub = DB::table('transactions as t')
            ->join('wallets as w', 't.wallet_id', '=', 'w.id')
            ->selectRaw('t.order_id, COALESCE(SUM(t.amount), 0) as repaid_amount')
            ->where('w.owner_type', TaiXe::class)
            ->whereIn('t.reference_type', ['cod_repayment', 'cod_reconciliation'])
            ->groupBy('t.order_id');

        $shopSettlementSub = DB::table('transactions as t')
            ->join('wallets as w', 't.wallet_id', '=', 'w.id')
            ->selectRaw('t.order_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN t.type = "credit" THEN t.amount ELSE -t.amount END), 0) as shop_paid_amount')
            ->selectRaw('MAX(CASE WHEN t.settlement_status = "transferred" THEN 2 WHEN t.settlement_status = "closed" THEN 1 ELSE 0 END) as settlement_stage')
            ->where('w.owner_type', Shop::class)
            ->whereIn('t.reference_type', ['cod_payment', 'service_fees'])
            ->groupBy('t.order_id');

        $query = DB::table('don_hang as dh')
            ->leftJoin('shops as s', 'dh.sender_id', '=', 's.id')
            ->leftJoin('tai_xe as tx', 'dh.tai_xe_id', '=', 'tx.id')
            ->leftJoinSub($cashCollectionSub, 'cc', function ($join) {
                $join->on('dh.id', '=', 'cc.order_id');
            })
            ->leftJoinSub($driverRepaymentSub, 'dr', function ($join) {
                $join->on('dh.id', '=', 'dr.order_id');
            })
            ->leftJoinSub($shopSettlementSub, 'ss', function ($join) {
                $join->on('dh.id', '=', 'ss.order_id');
            })
            ->where('dh.trang_thai_id', TrangThaiDonHang::DA_GIAO)
            ->whereBetween(DB::raw('COALESCE(dh.thoi_gian_hoan_thanh, dh.updated_at)'), [$startAt, $endAt]);

        if ($shopId) {
            $query->where('dh.sender_id', $shopId);
        }
        if ($driverId) {
            $query->where('dh.tai_xe_id', $driverId);
        }

        $rows = $query
            ->selectRaw('dh.id, dh.ma_don, dh.sender_id, dh.tai_xe_id')
            ->selectRaw('COALESCE(s.ten_shop, CONCAT("Shop #", dh.sender_id)) as shop_name')
            ->selectRaw('COALESCE(tx.ho_ten, CONCAT("Tài xế #", dh.tai_xe_id)) as driver_name')
            ->selectRaw('COALESCE(dh.cod_amount, 0) as cod_amount')
            ->selectRaw('COALESCE(dh.shipping_fee, 0) as shipping_fee')
            ->selectRaw("($codFeeExpr) as cod_fee")
            ->selectRaw('COALESCE(cc.cash_collected, 0) as cash_collected')
            ->selectRaw('COALESCE(dr.repaid_amount, 0) as repaid_amount')
            ->selectRaw('COALESCE(ss.shop_paid_amount, 0) as shop_paid_amount')
            ->selectRaw('COALESCE(ss.settlement_stage, 0) as settlement_stage')
            ->orderByDesc('dh.id')
            ->limit(200)
            ->get();

        return $rows->map(function ($row) {
            $targetCollection = (float) $row->cod_amount + (float) $row->shipping_fee;
            $pendingDriver = round($targetCollection - (float) $row->repaid_amount, 2);
            $expectedShopPayout = round((float) $row->cod_amount - (float) $row->shipping_fee - (float) $row->cod_fee, 2);
            $pendingTransfer = round($expectedShopPayout - (float) $row->shop_paid_amount, 2);

            $nextAction = 'Hoàn tất';
            $statusLabel = 'Đã hoàn tất';
            $statusTone = 'success';

            if ($pendingDriver > 0.01) {
                $nextAction = 'Xác nhận tài xế đã nộp';
                $statusLabel = 'Chờ tài xế nộp';
                $statusTone = 'warning';
            } elseif ($row->settlement_stage < 1) {
                $nextAction = 'Chốt kỳ đối soát';
                $statusLabel = 'Chờ chốt';
                $statusTone = 'info';
            } elseif ($row->settlement_stage < 2 || $pendingTransfer > 0.01) {
                $nextAction = 'Đánh dấu đã chuyển khoản';
                $statusLabel = 'Chờ chuyển khoản';
                $statusTone = 'primary';
            }

            $row->target_collection = $targetCollection;
            $row->pending_driver = max(0, $pendingDriver);
            $row->expected_shop_payout = $expectedShopPayout;
            $row->pending_transfer = max(0, $pendingTransfer);
            $row->status_label = $statusLabel;
            $row->status_tone = $statusTone;
            $row->next_action = $nextAction;

            return $row;
        });
    }
}
