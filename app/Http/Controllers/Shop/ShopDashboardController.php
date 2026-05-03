<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\Transaction;
use App\Models\TrangThaiDonHang;
use Illuminate\Support\Facades\Auth;

class ShopDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shop = $user->shop;

        if (!$shop) {
            return view('shop.dashboard', [
                'shop'         => null,
                'tongDon'      => 0,
                'dangGiao'     => 0,
                'daHoanThanh'  => 0,
                'wallet'       => null,
                'codBalance'   => 0,
                'recentOrders' => collect(),
            ]);
        }

        $shopId = $shop->id;

        $tongDon     = DonHang::where('sender_id', $shopId)->count();
        $dangGiao    = DonHang::where('sender_id', $shopId)
            ->whereIn('trang_thai_id', [TrangThaiDonHang::DA_LAY_HANG, TrangThaiDonHang::DANG_GIAO])
            ->count();
        $daHoanThanh = DonHang::where('sender_id', $shopId)->where('trang_thai_id', TrangThaiDonHang::DA_GIAO)->count();

        // Ví COD đối soát
        $wallet     = $shop->wallet;
        $codBalance = $wallet?->balance ?? 0;

        // Lịch sử giao dịch gần nhất
        $transactions = $wallet
            ? Transaction::where('wallet_id', $wallet->id)->latest()->take(10)->get()
            : collect();

        // 5 đơn gần nhất
        $recentOrders = DonHang::with(['khachHang', 'trangThai'])
            ->where('sender_id', $shopId)
            ->latest()
            ->take(5)
            ->get();

        return view('shop.dashboard', compact(
            'shop', 'tongDon', 'dangGiao', 'daHoanThanh',
            'wallet', 'codBalance', 'transactions', 'recentOrders'
        ));
    }
}
