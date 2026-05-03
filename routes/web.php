<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\DonHangController;
use App\Http\Controllers\Admin\TaiXeController;
use App\Http\Controllers\Admin\KhachHangController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\SystemFeeController;
use App\Http\Controllers\Admin\LoTrinhController;
use App\Http\Controllers\Admin\AdminSettlementController;
use App\Http\Controllers\Driver\DriverController;
use App\Http\Controllers\Api\DriverLocationController;
use App\Http\Controllers\Api\AddressController;

/*
|--------------------------------------------------------------------------
| API địa chỉ hành chính — Public (không cần auth)
| Dùng cho dropdown cascade: Tỉnh → Quận → Xã → Toạ độ
|--------------------------------------------------------------------------
*/
Route::prefix('api/address')->name('api.address.')->group(function () {
    Route::get('/provinces',   [AddressController::class, 'provinces'])->name('provinces');
    Route::get('/districts',   [AddressController::class, 'districts'])->name('districts');
    Route::get('/wards',       [AddressController::class, 'wards'])->name('wards');
    Route::post('/geocode',    [AddressController::class, 'geocode'])->name('geocode');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (login, register, logout, password reset...)
| Được tạo bởi Laravel Breeze — routes/auth.php
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Trang chủ
|   - Đã login  → redirect về dashboard theo role
|   - Chưa login → redirect về /login
| Không redirect thẳng vào admin.dashboard để tránh ERR_TOO_MANY_REDIRECTS
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    $user->loadMissing('role');

    return match($user->role?->name) {
        'Admin'    => redirect()->route('admin.dashboard'),
        'DieuPhoi' => redirect()->route('admin.don_hang.index'),
        'TaiXe'    => redirect()->route('driver.dashboard'),
        'Shop'     => redirect()->route('shop.dashboard'),
        default    => redirect()->route('admin.dashboard'),
    };
});

/*
|--------------------------------------------------------------------------
| Nhóm routes ADMIN — bảo vệ bằng 'auth' + 'role:Admin'
|
| Chỉ user có role = Admin mới truy cập được.
| Nếu chưa login    → redirect: /login
| Nếu sai role      → redirect: /admin/dashboard + flash error
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/doi-soat', [AdminSettlementController::class, 'index'])->name('settlement.index');
    Route::post('/doi-soat/chot-ky', [AdminSettlementController::class, 'close'])->name('settlement.close');
    Route::post('/doi-soat/chuyen-khoan', [AdminSettlementController::class, 'transfer'])->name('settlement.transfer');
    Route::post('/doi-soat/xac-nhan-tai-xe-nop', [AdminSettlementController::class, 'confirmDriverRepayment'])->name('settlement.confirm_driver_repayment');

    // ── CRUD: Khách hàng ──────────────────────────────────────────────────
    Route::resource('khach-hang', KhachHangController::class)
        ->parameters(['khach-hang' => 'khachHang'])
        ->names([
            'index'   => 'khach_hang.index',
            'create'  => 'khach_hang.create',
            'store'   => 'khach_hang.store',
            'edit'    => 'khach_hang.edit',
            'update'  => 'khach_hang.update',
            'destroy' => 'khach_hang.destroy',
        ]);

    // ── CRUD: Tài xế ─────────────────────────────────────────────────────
    Route::resource('tai-xe', TaiXeController::class)
        ->parameters(['tai-xe' => 'taiXe'])
        ->names([
            'index'   => 'tai_xe.index',
            'create'  => 'tai_xe.create',
            'store'   => 'tai_xe.store',
            'edit'    => 'tai_xe.edit',
            'update'  => 'tai_xe.update',
            'destroy' => 'tai_xe.destroy',
        ]);

    // ── CRUD: Đơn hàng ───────────────────────────────────────────────────
    Route::resource('don-hang', DonHangController::class)
        ->parameters(['don-hang' => 'donHang'])
        ->names([
            'index'   => 'don_hang.index',
            'create'  => 'don_hang.create',
            'store'   => 'don_hang.store',
            'show'    => 'don_hang.show',
            'edit'    => 'don_hang.edit',
            'update'  => 'don_hang.update',
            'destroy' => 'don_hang.destroy',
        ]);

    // Cập nhật trạng thái đơn hàng
    Route::post('/don-hang/{donHang}/update-status', [DonHangController::class, 'updateStatus'])
        ->name('don_hang.update_status');

    // Đối soát COD cho Shop
    Route::post('/don-hang/{donHang}/doi-soat', [DonHangController::class, 'doiSoat'])
        ->name('don_hang.doi_soat');

    // Gán tài xế cho đơn hàng
    Route::post('/don-hang/{donHang}/assign-driver', [DonHangController::class, 'assignDriver'])
        ->name('don_hang.assign_driver');

    // ── Bản đồ & Lộ trình ────────────────────────────────────────────────
    Route::get('/map',      [MapController::class,     'index'])->name('map.index');
    Route::get('/lo-trinh', [LoTrinhController::class, 'index'])->name('lo_trinh.index');
    Route::get('/lo-trinh/tai-xe/{taiXeId}', [LoTrinhController::class, 'show'])->name('lo_trinh.show');
    Route::get('/api/drivers/location', [DriverLocationController::class, 'getLocations'])->name('api.drivers.location');

    // ── CRUD: Users & Roles (Admin only) ─────────────────────────────────
    Route::resource('users', UserController::class)
        ->names([
            'index'   => 'users.index',
            'create'  => 'users.create',
            'store'   => 'users.store',
            'edit'    => 'users.edit',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
        ]);

    Route::resource('roles', RoleController::class)
        ->names([
            'index'   => 'roles.index',
            'create'  => 'roles.create',
            'store'   => 'roles.store',
            'edit'    => 'roles.edit',
            'update'  => 'roles.update',
            'destroy' => 'roles.destroy',
        ]);

    // ── Cấu hình phí ───────────────────────────────────────────────────────
    Route::get('/system-fees', [SystemFeeController::class, 'edit'])->name('system_fees.edit');
    Route::put('/system-fees', [SystemFeeController::class, 'update'])->name('system_fees.update');
});

/*
|--------------------------------------------------------------------------
| Nhóm routes ĐIỀU PHỐI VIÊN — 'auth' + 'role:Admin,DieuPhoi'
|
| Cả Admin và DieuPhoi đều có thể xem/quản lý đơn hàng từ prefix này.
| (Admin cũng có thể vào vì cùng dùng route admin.don_hang.*)
|--------------------------------------------------------------------------
*/
// Không cần group riêng vì DieuPhoi redirect đến admin.don_hang.index (đã có ở trên).
// Nếu muốn mở thêm quyền DieuPhoi, sửa middleware thành: 'role:Admin,DieuPhoi'

use App\Http\Controllers\Driver\DriverDashboardController;
use App\Http\Controllers\Driver\DriverOrderController;
use App\Http\Controllers\Driver\DriverRouteController;
use App\Http\Controllers\Driver\WalletController;

/*
|--------------------------------------------------------------------------
| Nhóm routes TÀI XẾ
| Prefix: /driver  |  Middleware: auth + role:TaiXe
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:TaiXe'])
    ->prefix('driver')
    ->name('driver.')
    ->group(function () {

    // ── Dashboard ──────────────────────────────────────────────────────
    Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');

    // ── Đơn hàng của tài xế ───────────────────────────────────────────
    Route::get('/orders',                          [DriverOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{id}',                     [DriverOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/update-status',      [DriverOrderController::class, 'updateStatus'])->name('orders.update_status');
    Route::post('/orders/{id}/upload-photo',       [DriverOrderController::class, 'uploadPhoto'])->name('orders.upload_photo');  // YC7

    // ── Ví tài xế ───────────────────────────────────────────────────────
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');

    // ── Lộ trình (Map) ────────────────────────────────────────────────
    // Route cũ giữ lại (DriverController::route) để không phá existing links
    Route::get('/route',              [DriverController::class, 'route'])->name('route');
    // Route mới: lộ trình hôm nay (DriverRouteController) — YC1, YC5, YC6
    Route::get('/route/today',        [DriverRouteController::class, 'today'])->name('route.today');
    // API: tối ưu lộ trình từ vị trí GPS tài xế
    Route::post('/route/optimize',    [DriverRouteController::class, 'optimize'])->name('route.optimize');
    // API: cập nhật vị trí GPS tài xế liên tục
    Route::post('/route/update-position', [DriverRouteController::class, 'updatePosition'])->name('route.update_position');

    // ── API Cập nhật vị trí ────────────────────────────────────────────
    Route::post('/api/driver/update-location', [DriverLocationController::class, 'updateLocation'])->name('api.driver.update_location');
});



/*
|--------------------------------------------------------------------------
| Nhom routes SHOP (Nguoi gui hang)
| Prefix: /shop  |  Middleware: auth + shop (role = 'Shop')
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Shop\ShopDashboardController;
use App\Http\Controllers\Shop\ShopDonHangController;
use App\Http\Controllers\Shop\ShopTaiChinhController;

Route::middleware(['auth', 'shop'])
    ->prefix('shop')
    ->name('shop.')
    ->group(function () {

    Route::get('/dashboard',         [ShopDashboardController::class, 'index'])->name('dashboard');
    Route::get('/don-hang',          [ShopDonHangController::class, 'index'])->name('don_hang.index');
    Route::get('/don-hang/tao-moi',  [ShopDonHangController::class, 'create'])->name('don_hang.create');
    Route::post('/don-hang',         [ShopDonHangController::class, 'store'])->name('don_hang.store');
    Route::get('/don-hang/{id}/sua', [ShopDonHangController::class, 'edit'])->name('don_hang.edit');
    Route::put('/don-hang/{id}',     [ShopDonHangController::class, 'update'])->name('don_hang.update');
    Route::delete('/don-hang/{id}',  [ShopDonHangController::class, 'destroy'])->name('don_hang.destroy');
    Route::get('/don-hang/{id}',     [ShopDonHangController::class, 'show'])->name('don_hang.show');
    Route::get('/tai-chinh',         [ShopTaiChinhController::class, 'index'])->name('tai_chinh.index');
});
