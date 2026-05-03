{{-- admin/tai_xe/create.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Thêm tài xế')

@section('content')
<div class="mb-4">
    <h1 class="page-heading">Thêm tài xế mới</h1>
    <p class="page-subtext">
        <a href="{{ route('admin.tai_xe.index') }}" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </p>
</div>

<div class="data-table-wrapper" style="max-width:720px;">
    <div class="p-4">
        <form action="{{ route('admin.tai_xe.store') }}" method="POST">
            @csrf
            @include('admin.tai_xe._form')
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-check-lg me-1"></i> Lưu tài xế
                </button>
                <a href="{{ route('admin.tai_xe.index') }}" class="btn btn-outline-secondary">Huỷ</a>
            </div>
        </form>
    </div>
</div>
@endsection
