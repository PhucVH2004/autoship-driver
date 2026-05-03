<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller xử lý đăng nhập / đăng xuất
 * Sau khi login thành công → redirect theo role của user
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Hiển thị trang login
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     *
     * Sau khi xác thực thành công, redirect theo role:
     *   Admin     → /admin/dashboard
     *   DieuPhoi  → /admin/don-hang
     *   TaiXe     → /driver/dashboard
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Xác thực thông tin đăng nhập
        $request->authenticate();

        // Regenerate session để bảo mật (chống session fixation)
        $request->session()->regenerate();

        // Lấy user vừa đăng nhập
        $user = Auth::user();

        // Load quan hệ role nếu chưa load
        $user->loadMissing('role');

        // Xác định URL redirect theo role
        $redirectUrl = $this->getRedirectByRole($user->role?->name);

        return redirect()->intended($redirectUrl);
    }

    /**
     * Xác định URL redirect dựa theo role name
     *
     * @param  string|null  $roleName  Tên role: Admin | DieuPhoi | TaiXe | Shop
     */
    private function getRedirectByRole(?string $roleName): string
    {
        return match($roleName) {
            'Admin'    => route('admin.dashboard'),
            'DieuPhoi' => route('admin.don_hang.index'),
            'TaiXe'    => route('driver.dashboard'),
            'Shop'     => route('shop.dashboard'),
            default    => route('admin.dashboard'),
        };
    }

    /**
     * Xử lý đăng xuất
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
