<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User — Tài khoản người dùng hệ thống
 *
 * Mỗi user thuộc 1 Role (Admin, DieuPhoi, TaiXe).
 * Kiểm tra role thông qua: $user->role->name
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Các trường có thể gán đại trà (mass assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'tai_xe_id',
        'trang_thai',
    ];

    /**
     * Ẩn các trường này khi serialize (trả về JSON)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kiểu dữ liệu tự động cast cho các trường
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── RELATIONSHIPS ──────────────────────────────────────────────────────

    /**
     * Quan hệ: 1 User thuộc 1 Role
     *
     * Cách dùng: $user->role->name  →  'Admin' | 'DieuPhoi' | 'TaiXe'
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Quan hệ: 1 User có thể liên kết với 1 Tài xế
     */
    public function taiXe()
    {
        return $this->belongsTo(TaiXe::class);
    }

    /**
     * Quan hệ: 1 User có thể là 1 Shop
     */
    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    // ─── HELPERS ────────────────────────────────────────────────────────────

    /**
     * Kiểm tra user có phải role cụ thể không
     * Ví dụ: $user->hasRole('Admin')
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Lấy URL redirect sau khi login dựa theo role
     */
    public function redirectAfterLogin(): string
    {
        return match($this->role?->name) {
            'Admin'    => route('admin.dashboard'),
            'DieuPhoi' => route('admin.don_hang.index'),
            'TaiXe'    => route('driver.dashboard'),
            'Shop'     => route('shop.dashboard'),
            default    => route('admin.dashboard'),
        };
    }
}
