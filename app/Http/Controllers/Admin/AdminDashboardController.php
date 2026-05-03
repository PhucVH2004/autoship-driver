<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\TrangThaiDonHang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $completedStatusId = TrangThaiDonHang::DA_GIAO;

        $totalRevenue = (float) DonHang::where('trang_thai_id', $completedStatusId)->sum('delivery_fee');

        $financialSource = $this->resolveFinancialSource();
        if ($financialSource === 'summary_view') {
            $totalPlatformFee = (float) DB::table('don_hang_financial_summary')
                ->where('trang_thai_id', $completedStatusId)
                ->sum('platform_fee');
            $totalDriverPaid = (float) DB::table('don_hang_financial_summary')
                ->where('trang_thai_id', $completedStatusId)
                ->sum('driver_real_income');
            $totalCodFee = (float) DB::table('don_hang_financial_summary')
                ->where('trang_thai_id', $completedStatusId)
                ->sum('cod_fee');
        } elseif ($financialSource === 'legacy_columns') {
            $totalPlatformFee = (float) DonHang::where('trang_thai_id', $completedStatusId)->sum('platform_fee');
            $totalDriverPaid = (float) DonHang::where('trang_thai_id', $completedStatusId)->sum('driver_real_income');
            $totalCodFee = (float) DonHang::where('trang_thai_id', $completedStatusId)->sum('cod_fee');
        } else {
            $totalPlatformFee = (float) DB::table('don_hang as dh')
                ->leftJoin('system_fees as sf', 'dh.system_fees_id', '=', 'sf.id')
                ->where('dh.trang_thai_id', $completedStatusId)
                ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dh.delivery_fee, 0) * COALESCE(sf.platform_ratio, 0), 2)), 0) as aggregate')
                ->value('aggregate');
            $totalDriverPaid = (float) DB::table('don_hang as dh')
                ->leftJoin('system_fees as sf', 'dh.system_fees_id', '=', 'sf.id')
                ->where('dh.trang_thai_id', $completedStatusId)
                ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dh.delivery_fee, 0) * COALESCE(sf.driver_ratio, 0) * (1 - COALESCE(sf.driver_tax_percent, 0)), 2)), 0) as aggregate')
                ->value('aggregate');
            $totalCodFee = (float) DB::table('don_hang as dh')
                ->leftJoin('system_fees as sf', 'dh.system_fees_id', '=', 'sf.id')
                ->where('dh.trang_thai_id', $completedStatusId)
                ->selectRaw('COALESCE(SUM(ROUND(COALESCE(dh.cod_amount, 0) * COALESCE(sf.cod_fee_percent, 0), 2)), 0) as aggregate')
                ->value('aggregate');
        }
        $totalCompletedOrders = (int) DonHang::where('trang_thai_id', $completedStatusId)->count();

        if ($financialSource === 'summary_view') {
            $topDrivers = DB::table('don_hang_financial_summary')
                ->select([
                    'tai_xe_id',
                    DB::raw('SUM(driver_real_income) as total_income'),
                    DB::raw('COUNT(*) as total_completed'),
                ])
                ->where('trang_thai_id', $completedStatusId)
                ->whereNotNull('tai_xe_id')
                ->groupBy('tai_xe_id')
                ->orderByDesc('total_income')
                ->limit(5)
                ->get();
        } elseif ($financialSource === 'legacy_columns') {
            $topDrivers = DB::table('don_hang')
                ->select([
                    'tai_xe_id',
                    DB::raw('SUM(driver_real_income) as total_income'),
                    DB::raw('COUNT(*) as total_completed'),
                ])
                ->where('trang_thai_id', $completedStatusId)
                ->whereNotNull('tai_xe_id')
                ->groupBy('tai_xe_id')
                ->orderByDesc('total_income')
                ->limit(5)
                ->get();
        } else {
            $topDrivers = DB::table('don_hang as dh')
                ->leftJoin('system_fees as sf', 'dh.system_fees_id', '=', 'sf.id')
                ->select([
                    'dh.tai_xe_id',
                    DB::raw('SUM(ROUND(COALESCE(dh.delivery_fee, 0) * COALESCE(sf.driver_ratio, 0) * (1 - COALESCE(sf.driver_tax_percent, 0)), 2)) as total_income'),
                    DB::raw('COUNT(*) as total_completed'),
                ])
                ->where('dh.trang_thai_id', $completedStatusId)
                ->whereNotNull('dh.tai_xe_id')
                ->groupBy('dh.tai_xe_id')
                ->orderByDesc('total_income')
                ->limit(5)
                ->get();
        }

        // Eager load taiXe cho topDrivers
        $driverIds = $topDrivers->pluck('tai_xe_id')->filter();
        $drivers = \App\Models\TaiXe::whereIn('id', $driverIds)->get()->keyBy('id');
        $topDrivers->each(function ($item) use ($drivers) {
            $item->taiXe = $drivers[$item->tai_xe_id] ?? null;
        });

        // Giữ lại dữ liệu cũ cho phần UI hiện tại (chart + recent orders)
        $tongDonHomNay = DonHang::whereDate('created_at', today())->count();
        $donDangGiao = DonHang::where('trang_thai_id', TrangThaiDonHang::DANG_GIAO)->count();
        $donHoanThanhHomNay = DonHang::where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
            ->whereDate('updated_at', today())
            ->count();

        $taiXeHoatDong = \App\Models\TaiXe::whereIn('trang_thai', ['Ranh', 'Dang giao'])->count();

        $donHangGanDay = DonHang::with(['khachHang', 'taiXe', 'trangThai'])
            ->latest()
            ->limit(5)
            ->get();

        $chartData = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo);
            return [
                'label' => $date->format('d/m'),
                'tong' => DonHang::whereDate('created_at', $date)->count(),
                'hoan_thanh' => DonHang::where('trang_thai_id', TrangThaiDonHang::DA_GIAO)
                    ->whereDate('updated_at', $date)
                    ->count(),
            ];
        });

        $thong_ke = [
            'tong_don_hom_nay' => $tongDonHomNay,
            'don_dang_giao' => $donDangGiao,
            'don_hoan_thanh' => $donHoanThanhHomNay,
            'tai_xe_hoat_dong' => $taiXeHoatDong,
        ];

        return view('admin.dashboard.index', compact(
            'thong_ke',
            'donHangGanDay',
            'chartData',
            'totalRevenue',
            'totalPlatformFee',
            'totalDriverPaid',
            'totalCodFee',
            'totalCompletedOrders',
            'topDrivers'
        ));
    }

    private function resolveFinancialSource(): string
    {
        if (Schema::hasTable('don_hang_financial_summary')) {
            return 'summary_view';
        }

        if (
            Schema::hasColumn('don_hang', 'platform_fee') &&
            Schema::hasColumn('don_hang', 'driver_real_income') &&
            Schema::hasColumn('don_hang', 'cod_fee')
        ) {
            return 'legacy_columns';
        }

        return 'computed_join';
    }
}

