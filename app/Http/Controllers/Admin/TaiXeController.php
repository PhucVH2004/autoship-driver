<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaiXe;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TaiXeController extends Controller
{
    public function index(Request $request)
    {
        $query = TaiXe::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%$search%")
                  ->orWhere('so_dien_thoai', 'like', "%$search%")
                  ->orWhere('bien_so_xe', 'like', "%$search%");
            });
        }

        if ($request->filled('trang_thai')) {
            $query->where('trang_thai', $request->trang_thai);
        }

        $taiXes = $query->paginate(15)->withQueryString();
        return view('admin.tai_xe.index', compact('taiXes'));
    }

    public function create()
    {
        return view('admin.tai_xe.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ho_ten'        => 'required|string|max:100',
            'so_dien_thoai' => 'required|string|max:20|unique:users,phone',
            'bien_so_xe'    => 'nullable|string|max:20|unique:tai_xe,bien_so_xe',
            'trang_thai'    => 'nullable|in:Ranh,Dang giao,Tam nghi',
            'mat_khau'      => 'required|string|min:6',
        ], [
            'ho_ten.required' => 'Vui lòng nhập họ tên.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
            'so_dien_thoai.unique' => 'Số điện thoại này đã được sử dụng.',
            'bien_so_xe.unique' => 'Biển số xe này đã tồn tại.',
            'mat_khau.required' => 'Vui lòng nhập mật khẩu.',
            'mat_khau.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        DB::beginTransaction();
        try {
            $taiXe = TaiXe::create($validated);

            $taiXeRole = Role::where('name', 'TaiXe')->first();

            if ($taiXeRole) {
                User::create([
                    'name' => $validated['ho_ten'],
                    'phone' => $validated['so_dien_thoai'],
                    'password' => Hash::make($validated['mat_khau']),
                    'role_id' => $taiXeRole->id,
                    'tai_xe_id' => $taiXe->id,
                    'trang_thai' => 'Hoat dong',
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.tai_xe.index')
                ->with('success', 'Đã thêm tài xế và tạo tài khoản thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra khi tạo tài xế và tài khoản: ' . $e->getMessage()]);
        }
    }

    public function edit(TaiXe $taiXe)
    {
        return view('admin.tai_xe.edit', compact('taiXe'));
    }

    public function update(Request $request, TaiXe $taiXe)
    {
        $validated = $request->validate([
            'ho_ten'        => 'required|string|max:100',
            'so_dien_thoai' => 'nullable|string|max:20',
            'bien_so_xe'    => 'nullable|string|max:20|unique:tai_xe,bien_so_xe,' . $taiXe->id,
            'trang_thai'    => 'nullable|in:Ranh,Dang giao,Tam nghi',
        ], [
            'ho_ten.required'   => 'Vui lòng nhập họ tên.',
            'bien_so_xe.unique' => 'Biển số xe này đã được dùng.',
        ]);

        $taiXe->update($validated);

        return redirect()
            ->route('admin.tai_xe.index')
            ->with('success', 'Đã cập nhật tài xế thành công!');
    }

    public function destroy(TaiXe $taiXe)
    {
        $taiXe->delete();

        return redirect()
            ->route('admin.tai_xe.index')
            ->with('success', 'Đã xoá tài xế thành công!');
    }
}
