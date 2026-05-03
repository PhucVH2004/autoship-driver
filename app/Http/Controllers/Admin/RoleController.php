<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller quản lý phân quyền (vai trò người dùng)
 *
 * Cột roles.name    : định danh cho middleware (Admin, DieuPhoi, TaiXe)
 * Cột roles.mo_ta   : mô tả hiển thị giao diện
 */
class RoleController extends Controller
{
    /**
     * Danh sách vai trò — kèm số lượng user thuộc mỗi role
     */
    public function index()
    {
        $roles = DB::table('roles')
            ->leftJoin('users', 'roles.id', '=', 'users.role_id')
            ->select('roles.*', DB::raw('COUNT(users.id) as so_nguoi'))
            ->groupBy('roles.id', 'roles.name', 'roles.mo_ta', 'roles.created_at', 'roles.updated_at')
            ->orderBy('roles.id')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Form tạo vai trò mới
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Lưu vai trò mới vào DB
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:50|unique:roles,name',
            'mo_ta' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên vai trò.',
            'name.unique'   => 'Tên vai trò này đã tồn tại.',
        ]);

        Role::create([
            'name'  => $request->name,
            'mo_ta' => $request->mo_ta,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', "Đã thêm vai trò '{$request->name}' thành công!");
    }

    /**
     * Form sửa vai trò
     */
    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Cập nhật vai trò
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'  => "required|string|max:50|unique:roles,name,{$role->id}",
            'mo_ta' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên vai trò.',
            'name.unique'   => 'Tên vai trò này đã tồn tại.',
        ]);

        $role->update([
            'name'  => $request->name,
            'mo_ta' => $request->mo_ta,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã cập nhật vai trò thành công!');
    }

    /**
     * Xoá vai trò — kiểm tra có user nào đang dùng không
     */
    public function destroy(Role $role)
    {
        $soNguoi = DB::table('users')->where('role_id', $role->id)->count();

        if ($soNguoi > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Không thể xoá: có $soNguoi người dùng đang dùng vai trò này!");
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Đã xoá vai trò thành công!');
    }
}
