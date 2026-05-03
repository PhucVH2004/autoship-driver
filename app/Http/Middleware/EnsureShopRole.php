<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role?->name !== 'Shop') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Không có quyền truy cập.'], 403);
            }
            return redirect()->route('login')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}

