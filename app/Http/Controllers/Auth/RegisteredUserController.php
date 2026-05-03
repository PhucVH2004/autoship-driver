<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * User mới đăng ký tự gán role mặc định là 'TaiXe'.
     * Sau khi đăng ký → redirect theo role.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Gán role mặc định cho user mới (TaiXe)
        // Admin tạo tài khoản DieuPhoi/Admin phải làm qua trang quản lý Users
        $defaultRole = Role::where('name', 'TaiXe')->first();

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => $defaultRole?->id, // null nếu chưa seed (tránh crash)
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redirect theo role sau khi đăng ký
        $user->loadMissing('role');

        $redirectUrl = match($user->role?->name) {
            'Admin'    => route('admin.dashboard'),
            'DieuPhoi' => route('admin.don_hang.index'),
            'TaiXe'    => route('driver.orders'),
            default    => route('admin.dashboard'),
        };

        return redirect($redirectUrl);
    }
}
