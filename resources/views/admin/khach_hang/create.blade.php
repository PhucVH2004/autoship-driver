{{-- admin/khach_hang/create.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Thêm khách hàng')

@section('content')
<div class="mb-4">
    <h1 class="page-heading">Thêm khách hàng mới</h1>
    <p class="page-subtext">
        <a href="{{ route('admin.khach_hang.index') }}" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </p>
</div>

<div class="data-table-wrapper" style="max-width:720px;">
    <div class="p-4">
        <form action="{{ route('admin.khach_hang.store') }}" method="POST">
            @csrf
            @include('admin.khach_hang._form')
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check-lg me-1"></i> Lưu khách hàng
                </button>
                <a href="{{ route('admin.khach_hang.index') }}" class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
