{{-- admin/don_hang/create.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Tạo đơn hàng')

@section('content')
<div class="mb-4">
    <h1 class="page-heading">Tạo đơn hàng mới</h1>
    <p class="page-subtext">
        <a href="{{ route('admin.don_hang.index') }}" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </p>
</div>

<div class="data-table-wrapper" style="max-width:720px;">
    <div class="p-4">
        <form action="{{ route('admin.don_hang.store') }}" method="POST">
            @csrf
            @include('admin.don_hang._form', ['khachHangs' => $khachHangs, 'taiXes' => $taiXes])
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check-lg me-1"></i> Tạo đơn hàng
                </button>
                <a href="{{ route('admin.don_hang.index') }}" class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
