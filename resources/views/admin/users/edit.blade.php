{{-- admin/users/edit.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Sửa người dùng')
@section('content')
<div class="mb-4">
    <h1 class="page-heading">Sửa tài khoản: {{ $user->name }}</h1>
    <p class="page-subtext"><a href="{{ route('admin.users.index') }}" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Quay lại</a></p>
</div>
<div class="data-table-wrapper" style="max-width:580px;">
    <div class="table-header"><h5><i class="bi bi-person-gear me-2 text-primary"></i>Chỉnh sửa tài khoản</h5></div>
    <div class="p-4">
        @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:.88rem;">Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:.88rem;">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label class="form-label fw-bold" style="font-size:.88rem;">Mật khẩu mới <span class="text-muted fw-normal">(bỏ trống = giữ nguyên)</span></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Ít nhất 6 ký tự">
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-bold" style="font-size:.88rem;">Xác nhận mật khẩu</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu mới">
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <label class="form-label fw-bold" style="font-size:.88rem;">Vai trò <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                        <option value="">— Chọn vai trò —</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-bold" style="font-size:.88rem;">Trạng thái</label>
                    <select name="trang_thai" class="form-select">
                        <option value="Hoat dong" {{ old('trang_thai', $user->trang_thai) === 'Hoat dong' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="Khoa" {{ old('trang_thai', $user->trang_thai) === 'Khoa' ? 'selected' : '' }}>Bị khoá</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-check2 me-1"></i>Cập nhật</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-3">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
