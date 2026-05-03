<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\Transaction;
use App\Models\TrangThaiDonHang;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class DriverDashboardController extends Controller
{
    /**
     * Dashboard tài xế — thống kê 4 chỉ số (YC4)
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $taiXe = $user->taiXe;
        $taiXeId = $taiXe?->id;

        $wallet = Wallet::firstOrCreate([
            'owner_type' => get_class($taiXe),
            'owner_id'   => $taiXeId,
        ], [
            'balance'  => 0,
            'currency' => 'VND',
        ]);

        $walletOutstandingAmount = $wallet->balance < 0 ? abs((float) $wallet->balance) : 0;
        $walletSurplusAmount = $wallet->balance > 0 ? (float) $wallet->balance : 0;

        $walletStatusLabel = match (true) {
            $wallet->balance < 0 => 'Còn phải nộp',
            $wallet->balance > 0 => 'Đang dư',
            default => 'Đã cân bằng',
        };

        $walletStatusHelp = match (true) {
            $wallet->balance < 0 => 'Bạn đang giữ tiền COD cần nộp lại hệ thống.',
            $wallet->balance > 0 => 'Ví đang dư sau đối soát hoặc thưởng.',
            default => 'Không còn khoản chênh lệch cần đối soát.',
        };

        $kpiToday = (float) Transaction::where('wallet_id', $wallet->id)
            ->where('reference_type', 'delivery_reward')
            ->whereDate('created_at', today())
            ->sum('amount');

        $financialSource = $this->resolveFinancialSource();
        if ($financialSource === 'summary_view') {
            $earningsToday = (float) DB::table('don_hang_financial_summary')
                ->where('tai_xe_id', $taiXeId)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->whereDate('thoi_gian_hoan_thanh', today())
                ->sum('driver_real_income');

            $earningsWeek = (float) DB::table('don_hang_financial_summary')
                ->where('tai_xe_id', $taiXeId)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->whereBetween('thoi_gian_hoan_thanh', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('driver_real_income');

            $earningsMonth = (float) DB::table('don_hang_financial_summary')
                ->where('tai_xe_id', $taiXeId)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->whereBetween('thoi_gian_hoan_thanh', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('driver_real_income');
        } elseif ($financialSource === 'legacy_columns') {
            $earningsToday = (float) DonHang::where('tai_xe_id', $taiXeId)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->whereDate('thoi_gian_hoan_thanh', today())
                ->sum('driver_real_income');

            $earningsWeek = (float) DonHang::where('tai_xe_id', $taiXeId)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->whereBetween('thoi_gian_hoan_thanh', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('driver_real_income');

            $earningsMonth = (float) DonHang::where('tai_xe_id', $taiXeId)
                ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                ->whereBetween('thoi_gian_hoan_thanh', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('driver_real_income');
        } else {
            $earningsToday = $this->sumComputedDriverIncome($taiXeId, function ($query) {
                $query->whereDate('dh.thoi_gian_hoan_thanh', today());
            });

            $earningsWeek = $this->sumComputedDriverIncome($taiXeId, function ($query) {
                $query->whereBetween('dh.thoi_gian_hoan_thanh', [now()->startOfWeek(), now()->endOfWeek()]);
            });

            $earningsMonth = $this->sumComputedDriverIncome($taiXeId, function ($query) {
                $query->whereBetween('dh.thoi_gian_hoan_thanh', [now()->startOfMonth(), now()->endOfMonth()]);
            });
        }

        $totalCompletedOrders = (int) DonHang::where('tai_xe_id', $taiXeId)
            ->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
            ->count();

        $validator = Validator::make($request->all(), [
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ], [
            'end_date.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.date'   => 'Ngày kết thúc không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('driver.dashboard')
                ->withErrors($validator)
                ->withInput();
        }

        $startDate = $request->input('start_date', today()->format('Y-m-d'));
        $endDate   = $request->input('end_date', today()->format('Y-m-d'));

        $baseQuery = DonHang::with('trangThai')
            ->where('tai_xe_id', $taiXeId)
            ->where('created_at', '<=', $endDate . ' 23:59:59')
            ->where(function ($query) use ($startDate) {
                $query->whereNotIn('trang_thai_id', TrangThaiDonHang::doneStatuses())
                    ->orWhere('thoi_gian_hoan_thanh', '>=', $startDate . ' 00:00:00')
                    ->orWhere(function ($sub) use ($startDate) {
                        $sub->whereIn('trang_thai_id', TrangThaiDonHang::doneStatuses())
                            ->whereNull('thoi_gian_hoan_thanh')
                            ->where('updated_at', '>=', $startDate . ' 00:00:00');
                    });
            });

        $validOrders = (clone $baseQuery)->get();

        $tongDonHomNay = $validOrders->count();
        $danhSachXong = TrangThaiDonHang::doneStatuses();
        $trangThaiDangXuLy = [TrangThaiDonHang::DA_LAY_HANG, TrangThaiDonHang::DANG_GIAO, TrangThaiDonHang::HOAN];
        $trangThaiGiaoThanhCong = TrangThaiDonHang::DA_GIAO;
        $danhSachGiaiQuyetTrongKy = [TrangThaiDonHang::DA_GIAO, TrangThaiDonHang::HUY, TrangThaiDonHang::DA_HOAN];

        $daGiaiQuyetTrongKy = $validOrders->filter(function ($d) use ($startDate, $endDate, $danhSachGiaiQuyetTrongKy) {
            if (in_array($d->trang_thai_id, $danhSachGiaiQuyetTrongKy, true)) {
                $finishedDate = $d->thoi_gian_hoan_thanh
                    ? \Carbon\Carbon::parse($d->thoi_gian_hoan_thanh)
                    : \Carbon\Carbon::parse($d->updated_at);

                $finished = $finishedDate->format('Y-m-d');
                return $finished >= $startDate && $finished <= $endDate;
            }

            return false;
        })->count();

        $daHoanThanh = $validOrders->filter(function ($d) use ($startDate, $endDate, $trangThaiGiaoThanhCong) {
            if ($d->trang_thai_id === $trangThaiGiaoThanhCong) {
                $finishedDate = $d->thoi_gian_hoan_thanh
                    ? \Carbon\Carbon::parse($d->thoi_gian_hoan_thanh)
                    : \Carbon\Carbon::parse($d->updated_at);

                $finished = $finishedDate->format('Y-m-d');
                return $finished >= $startDate && $finished <= $endDate;
            }

            return false;
        })->count();

        $conLai = $tongDonHomNay - $daGiaiQuyetTrongKy;

        $dangGiao = $validOrders->filter(function ($d) use ($startDate, $endDate, $danhSachGiaiQuyetTrongKy, $trangThaiDangXuLy) {
            if (in_array($d->trang_thai_id, $danhSachGiaiQuyetTrongKy, true)) {
                $finishedDate = $d->thoi_gian_hoan_thanh
                    ? \Carbon\Carbon::parse($d->thoi_gian_hoan_thanh)
                    : \Carbon\Carbon::parse($d->updated_at);

                $finished = $finishedDate->format('Y-m-d');
                if ($finished >= $startDate && $finished <= $endDate) {
                    return false;
                }
            }

            return in_array($d->trang_thai_id, $trangThaiDangXuLy, true);
        })->count();

        $donHangGanNhat = DonHang::with(['khachHang', 'trangThai'])
            ->where('tai_xe_id', $taiXeId)
            ->whereNotIn('trang_thai_id', $danhSachXong)
            ->latest()
            ->take(5)
            ->get();

        return view('driver.dashboard', compact(
            'tongDonHomNay',
            'dangGiao',
            'daHoanThanh',
            'conLai',
            'donHangGanNhat',
            'wallet',
            'walletOutstandingAmount',
            'walletSurplusAmount',
            'walletStatusLabel',
            'walletStatusHelp',
            'kpiToday',
            'earningsToday',
            'earningsWeek',
            'earningsMonth',
            'totalCompletedOrders'
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

    private function sumComputedDriverIncome(int $taiXeId, callable $dateConstraint): float
    {
        $query = DB::table('don_hang as dh')
            ->leftJoin('system_fees as sf', 'dh.system_fees_id', '=', 'sf.id')
            ->where('dh.tai_xe_id', $taiXeId)
            ->where('dh.trang_thai_id', TrangThaiDonHang::DA_GIAO);

        $dateConstraint($query);

        return (float) $query
            ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dh.delivery_fee, 0) * COALESCE(sf.driver_ratio, 0) * (1 - COALESCE(sf.driver_tax_percent, 0)), 2)), 0) as aggregate')
            ->value('aggregate');
    }
}
