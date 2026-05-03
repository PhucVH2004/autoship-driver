<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ShopTaiChinhController extends Controller
{
    public function index(Request $request)
    {
        $shop   = Auth::user()->shop;
        $wallet = $shop?->getOrCreateWallet();

        $codBalance = $wallet?->balance ?? 0;

        $filters = $this->resolveFilters($request);

        $transactionsQuery = Transaction::query()
            ->with(['donHang', 'settledByUser', 'transferredByUser'])
            ->where('wallet_id', $wallet?->id ?? 0);

        if ($filters['date_from']) {
            $transactionsQuery->where('created_at', '>=', $filters['date_from']->copy()->startOfDay());
        }
        if ($filters['date_to']) {
            $transactionsQuery->where('created_at', '<=', $filters['date_to']->copy()->endOfDay());
        }
        if ($filters['reference_type']) {
            if ($filters['reference_type'] === 'reconciliation') {
                $transactionsQuery->whereIn('reference_type', ['cod_repayment', 'cod_reconciliation']);
            } else {
                $transactionsQuery->where('reference_type', $filters['reference_type']);
            }
        }
        if ($filters['settlement_status']) {
            if ($filters['settlement_status'] === Transaction::SETTLEMENT_PENDING) {
                $transactionsQuery->where(function ($query) {
                    $query->whereNull('settlement_status')
                        ->orWhere('settlement_status', Transaction::SETTLEMENT_PENDING);
                });
            } else {
                $transactionsQuery->where('settlement_status', $filters['settlement_status']);
            }
        }
        if ($filters['order_code']) {
            $orderCode = $filters['order_code'];
            $transactionsQuery->whereHas('donHang', function ($query) use ($orderCode) {
                $query->where('ma_don', 'like', '%' . $orderCode . '%');
            });
        }
        if ($filters['amount_min'] !== null) {
            $transactionsQuery->where('amount', '>=', $filters['amount_min']);
        }
        if ($filters['amount_max'] !== null) {
            $transactionsQuery->where('amount', '<=', $filters['amount_max']);
        }
        if ($filters['keyword']) {
            $keyword = $filters['keyword'];
            $transactionsQuery->where(function ($query) use ($keyword) {
                $query->where('description', 'like', '%' . $keyword . '%')
                    ->orWhere('reference_type', 'like', '%' . $keyword . '%')
                    ->orWhereHas('donHang', function ($sub) use ($keyword) {
                        $sub->where('ma_don', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $sortable = ['created_at', 'amount', 'settlement_status'];
        $sortBy = in_array($filters['sort_by'], $sortable, true) ? $filters['sort_by'] : 'created_at';
        $sortDir = $filters['sort_dir'] === 'asc' ? 'asc' : 'desc';
        $transactionsQuery->orderBy($sortBy, $sortDir)->orderByDesc('id');

        if ($request->query('export') === 'csv' || $request->query('export') === 'excel') {
            return $this->exportTransactions($transactionsQuery, $request->query('export'));
        }

        $summary = (clone $transactionsQuery)
            ->reorder()
            ->selectRaw('COALESCE(SUM(CASE WHEN type = "credit" THEN amount ELSE 0 END), 0) as total_in')
            ->selectRaw('COALESCE(SUM(CASE WHEN type = "debit" THEN amount ELSE 0 END), 0) as total_fee')
            ->selectRaw('COALESCE(SUM(CASE WHEN type = "credit" THEN amount ELSE -amount END), 0) as net_amount')
            ->selectRaw('COUNT(*) as total_transactions')
            ->first();

        $orderReconciliation = (clone $transactionsQuery)
            ->reorder()
            ->whereNotNull('order_id')
            ->selectRaw('COUNT(DISTINCT CASE WHEN settlement_status IN ("closed", "transferred") THEN order_id END) as reconciled_orders')
            ->selectRaw('COUNT(DISTINCT CASE WHEN settlement_status = "pending" OR settlement_status IS NULL THEN order_id END) as pending_orders')
            ->first();

        $transactions = $transactionsQuery
            ->paginate($filters['per_page'])
            ->withQueryString();

        return view('shop.tai_chinh.index', compact(
            'shop',
            'wallet',
            'codBalance',
            'transactions',
            'summary',
            'orderReconciliation',
            'filters'
        ));
    }

    private function resolveFilters(Request $request): array
    {
        $dateFrom = $request->filled('date_from') && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('date_from'))
            ? Carbon::createFromFormat('Y-m-d', (string) $request->input('date_from'))
            : null;
        $dateTo = $request->filled('date_to') && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input('date_to'))
            ? Carbon::createFromFormat('Y-m-d', (string) $request->input('date_to'))
            : null;

        $amountMin = $request->filled('amount_min') ? (float) $request->input('amount_min') : null;
        $amountMax = $request->filled('amount_max') ? (float) $request->input('amount_max') : null;

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'reference_type' => $request->input('reference_type'),
            'settlement_status' => $request->input('settlement_status'),
            'order_code' => trim((string) $request->input('order_code')),
            'amount_min' => $amountMin,
            'amount_max' => $amountMax,
            'keyword' => trim((string) $request->input('keyword')),
            'sort_by' => (string) $request->input('sort_by', 'created_at'),
            'sort_dir' => (string) $request->input('sort_dir', 'desc'),
            'per_page' => max(10, min(100, (int) $request->input('per_page', 20))),
        ];
    }

    private function exportTransactions($query, string $format): StreamedResponse
    {
        $filename = 'shop-tai-chinh-' . now()->format('Ymd_His') . ($format === 'excel' ? '.xls' : '.csv');

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Ngay', 'Ma don', 'Mo ta', 'Loai', 'Reference', 'Trang thai doi soat', 'So tien']);

            $query->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $tx) {
                    fputcsv($out, [
                        optional($tx->created_at)->format('d/m/Y H:i'),
                        $tx->donHang?->ma_don ?? '',
                        $tx->description,
                        $tx->type,
                        $tx->reference_type,
                        $tx->settlement_status ?? 'pending',
                        (float) $tx->amount,
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => $format === 'excel'
                ? 'application/vnd.ms-excel; charset=UTF-8'
                : 'text/csv; charset=UTF-8',
        ]);
    }
}
