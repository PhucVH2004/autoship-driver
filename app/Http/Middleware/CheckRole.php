<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckRole — Phân quyền theo vai trò người dùng
 *
 * Sử dụng trong route:
 *   ->middleware(['auth', 'role:Admin'])
 *   ->middleware(['auth', 'role:Admin,DieuPhoi'])   // nhiều role cách nhau dấu phẩy
 *
 * Nếu user không có role phù hợp → redirect về dashboard.
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  $role  Tên role cần kiểm tra (hỗ trợ nhiều role cách nhau bằng dấu phẩy)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Kiểm tra đã đăng nhập chưa (dự phòng — thường middleware 'auth' đã lo)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Kiểm tra user có role không
        if (!$user->role) {
            abort(403, 'Tài khoản chưa được gán vai trò.');
        }

        // Lấy tên role hiện tại của user
        $userRole = $user->role->name;

        // Kiểm tra role có trong danh sách được phép không
        if (!in_array($userRole, $roles)) {
            // Redirect về dashboard phù hợp với role của user (tránh redirect loop)
            $redirectRoute = match($userRole) {
                'Admin'    => 'admin.dashboard',
                'DieuPhoi' => 'admin.don_hang.index',
                'TaiXe'    => 'driver.dashboard',
                'Shop'     => 'shop.dashboard',
                default    => 'login',
            };

            return redirect()->route($redirectRoute)
                ->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
