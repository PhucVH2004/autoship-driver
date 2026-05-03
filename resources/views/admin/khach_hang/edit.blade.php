{{-- admin/khach_hang/edit.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Sửa khách hàng')

@section('content')
<div class="mb-4">
    <h1 class="page-heading">Sửa khách hàng: {{ $khachHang->ho_ten }}</h1>
    <p class="page-subtext">
        <a href="{{ route('admin.khach_hang.index') }}" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </p>
</div>

<div class="data-table-wrapper" style="max-width:720px;">
    <div class="p-4">
        <form action="{{ route('admin.khach_hang.update', $khachHang) }}" method="POST">
            @csrf @method('PUT')
            @include('admin.khach_hang._form', ['model' => $khachHang])
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check-lg me-1"></i> Cập nhật
                </button>
                <a href="{{ route('admin.khach_hang.index') }}" class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
