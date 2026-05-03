{{-- admin/roles/create.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Thêm vai trò')
@section('content')
<div class="mb-4">
    <h1 class="page-heading">Thêm vai trò mới</h1>
    <p class="page-subtext"><a href="{{ route('admin.roles.index') }}" class="text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Quay lại</a></p>
</div>
<div class="data-table-wrapper" style="max-width:540px;">
    <div class="table-header"><h5><i class="bi bi-shield-plus me-2 text-primary"></i>Thông tin vai trò</h5></div>
    <div class="p-4">
        @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-4">{{ $errors->first() }}</div>
        @endif
        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold" style="font-size:.88rem;">Tên vai trò <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" placeholder="VD: Admin, DieuPhoi, TaiXe...">
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold" style="font-size:.88rem;">Mô tả</label>
                <textarea name="mo_ta" class="form-control" rows="3" placeholder="Mô tả ngắn về vai trò này...">{{ old('mo_ta') }}</textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3"><i class="bi bi-check2 me-1"></i>Lưu vai trò</button>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary rounded-3">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
