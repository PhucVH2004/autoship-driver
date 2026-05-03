<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Model Role — Quản lý vai trò người dùng
 *
 * Các role hiện tại:
 *   - Admin    : quản trị viên toàn quyền
 *   - DieuPhoi : điều phối viên quản lý đơn hàng
 *   - TaiXe    : tài xế giao hàng
 */
class Role extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán đại trà (mass assignment)
     */
    protected $fillable = ['name'];

    /**
     * Quan hệ: 1 Role có nhiều User
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
