{{--
    TRANG QUẢN LÝ KHÁCH HÀNG — kết nối DB thật (cột: ten_khach)
--}}
@extends('layouts.admin')

@section('page_title', 'Quản lý khách hàng')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading">Quản lý khách hàng</h1>
        <p class="page-subtext">Danh sách khách hàng đã đặt đơn hàng</p>
    </div>
    <a href="{{ route('admin.khach_hang.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-1"></i> Thêm khách hàng
    </a>
</div>

<form method="GET" action="{{ route('admin.khach_hang.index') }}" class="mb-4">
    <div class="input-group" style="max-width:400px;">
        <input type="text" name="search" class="form-control bg-light border-0 rounded-start-3"
               placeholder="Tìm tên, SĐT, địa chỉ..." value="{{ request('search') }}" style="font-size:0.88rem;">
        <button class="btn btn-primary rounded-end-3" type="submit"><i class="bi bi-search"></i></button>
        @if(request('search'))
            <a href="{{ route('admin.khach_hang.index') }}" class="btn btn-outline-secondary rounded-3 ms-2">
                <i class="bi bi-x"></i>
            </a>
        @endif
    </div>
</form>

<div class="data-table-wrapper">
    <div class="table-header">
        <h5><i class="bi bi-people me-2 text-primary"></i>Danh sách khách hàng
            <span class="badge bg-primary ms-1">{{ $khachHangs->total() }}</span>
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Tổng đơn</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @if(count($khachHangs) > 0)
                @foreach ($khachHangs as $kh)
                <tr>
                    <td class="text-muted">{{ $kh->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold"
                                style="width:34px;height:34px;font-size:0.8rem;flex-shrink:0;">
                                {{ strtoupper(mb_substr($kh->ten_khach, 0, 1)) }}
                            </div>
                            <strong>{{ $kh->ten_khach }}</strong>
                        </div>
                    </td>
                    <td>{{ $kh->so_dien_thoai }}</td>
                    <td style="font-size:0.85rem; color:#64748b; max-width:200px;">{{ $kh->dia_chi }}</td>
                    <td><strong>{{ $kh->donHangs->count() }}</strong></td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.khach_hang.edit', $kh) }}"
                               class="btn btn-sm btn-outline-warning rounded-2" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.khach_hang.destroy', $kh) }}" method="POST"
                                  onsubmit="return confirm('Xoá khách hàng này?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" title="Xoá">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-2 d-block mb-2"></i>
                        Không có khách hàng nào. <a href="{{ route('admin.khach_hang.create') }}">Thêm ngay</a>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if($khachHangs->hasPages())
    <div class="px-4 py-3" style="border-top:1px solid #f1f5f9;">
        {{ $khachHangs->links() }}
    </div>
    @endif
</div>

@endsection
