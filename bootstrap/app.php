<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;

/**
 * Bootstrap ứng dụng Laravel 12
 *
 * KHÁC với Laravel 10/11: Không dùng app/Http/Kernel.php
 * Middleware được đăng ký tại đây qua ->withMiddleware()
 */
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /**
         * Đăng ký middleware alias cho phân quyền theo role.
         *
         * Cách dùng trong routes/web.php:
         *   ->middleware(['auth', 'role:Admin'])
         *   ->middleware(['auth', 'role:Admin,DieuPhoi'])
         */
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'shop' => \App\Http\Middleware\EnsureShopRole::class,
        ]);

        /**
         * Fix ERR_TOO_MANY_REDIRECTS:
         *
         * Khi user đã đăng nhập mà cố vào /login hoặc /register,
         * middleware 'guest' sẽ redirect theo role của user.
         *
         * Nếu không đăng ký callback này, Laravel sẽ:
         *   1. Tìm route tên 'dashboard' → không có (chỉ có 'admin.dashboard')
         *   2. Fallback về '/'
         *   3. '/' redirect về admin.dashboard → chưa login → /login → lặp vô tận
         */
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $user = Auth::user();

            if (!$user) {
                return route('login');
            }

            $user->loadMissing('role');

            return match($user->role?->name) {
                'Admin'    => route('admin.dashboard'),
                'DieuPhoi' => route('admin.don_hang.index'),
                'TaiXe'    => route('driver.orders'),
                'Shop'     => route('shop.dashboard'),
                default    => route('admin.dashboard'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
