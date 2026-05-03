<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Danh sách người dùng — eager load role, tìm kiếm
     */
    public function index(Request $request)
    {
        $query = User::select('users.*', 'roles.name as role_name')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->latest('users.created_at');

        // Tìm kiếm theo tên hoặc email
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('users.name', 'like', "%$s%")
                                      ->orWhere('users.email', 'like', "%$s%"));
        }

        // Lọc theo role
        if ($request->filled('role_id')) {
            $query->where('users.role_id', $request->role_id);
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = DB::table('roles')->orderBy('id')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Form tạo user mới
     */
    public function create()
    {
        $roles = DB::table('roles')->orderBy('id')->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Lưu user mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role_id'  => 'required|exists:roles,id',
            'trang_thai' => 'required|in:Hoat dong,Khoa',
        ], [
            'name.required'     => 'Vui lòng nhập họ tên.',
            'email.unique'      => 'Email này đã được sử dụng.',
            'password.min'      => 'Mật khẩu ít nhất 6 ký tự.',
            'password.confirmed'=> 'Xác nhận mật khẩu không khớp.',
            'role_id.required'  => 'Vui lòng chọn vai trò.',
        ]);

        User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role_id'    => $request->role_id,
            'trang_thai' => $request->trang_thai,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Đã tạo tài khoản '{$request->name}' thành công!");
    }

    /**
     * Form sửa user
     */
    public function edit(User $user)
    {
        $roles = DB::table('roles')->orderBy('id')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Cập nhật user (không đổi mật khẩu nếu để trống)
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => "required|email|unique:users,email,{$user->id}",
            'password'   => 'nullable|string|min:6|confirmed',
            'role_id'    => 'required|exists:roles,id',
            'trang_thai' => 'required|in:Hoat dong,Khoa',
        ], [
            'name.required'  => 'Vui lòng nhập họ tên.',
            'email.unique'   => 'Email này đã được sử dụng.',
            'role_id.required' => 'Vui lòng chọn vai trò.',
        ]);

        $data = [
            'name'       => $request->name,
            'email'      => $request->email,
            'role_id'    => $request->role_id,
            'trang_thai' => $request->trang_thai,
        ];

        // Chỉ đổi password nếu user nhập
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', "Đã cập nhật tài khoản '{$user->name}' thành công!");
    }

    /**
     * Xoá user
     */
    public function destroy(User $user)
    {
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', "Đã xoá tài khoản '$name'!");
    }
}
