{{--
    TRANG QUẢN LÝ TÀI XẾ — kết nối DB thật
--}}
@extends('layouts.admin')

@section('page_title', 'Quản lý tài xế')

@section('content')

{{-- Flash message --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading">Quản lý tài xế</h1>
        <p class="page-subtext">Danh sách tài xế đã đăng ký trong hệ thống</p>
    </div>
    <a href="{{ route('admin.tai_xe.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-1"></i> Thêm tài xế
    </a>
</div>

{{-- Tìm kiếm + Lọc trạng thái --}}
<form method="GET" action="{{ route('admin.tai_xe.index') }}" class="d-flex gap-3 mb-4 flex-wrap">
    <div class="input-group" style="max-width:360px;">
        <input type="text" name="search" class="form-control bg-light border-0 rounded-start-3"
               placeholder="Tìm tên, SĐT, biển số..."
               value="{{ request('search') }}" style="font-size:0.88rem;">
        <button class="btn btn-primary rounded-end-3" type="submit"><i class="bi bi-search"></i></button>
    </div>
    <select name="trang_thai" class="form-select rounded-3 border-0 bg-light" style="max-width:180px;font-size:0.88rem;"
            onchange="this.form.submit()">
        <option value="">Tất cả trạng thái</option>
        <option value="dang_giao" {{ request('trang_thai') == 'dang_giao' ? 'selected' : '' }}>Đang giao</option>
        <option value="ranh"      {{ request('trang_thai') == 'ranh'      ? 'selected' : '' }}>Rảnh</option>
        <option value="nghi"      {{ request('trang_thai') == 'nghi'      ? 'selected' : '' }}>Nghỉ</option>
    </select>
    @if(request('search') || request('trang_thai'))
        <a href="{{ route('admin.tai_xe.index') }}" class="btn btn-outline-secondary rounded-3">
            <i class="bi bi-x"></i> Xoá lọc
        </a>
    @endif
</form>

<div class="data-table-wrapper">
    <div class="table-header">
        <h5><i class="bi bi-person-badge me-2 text-primary"></i>Danh sách tài xế
            <span class="badge bg-primary ms-1">{{ $taiXes->total() }}</span>
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Họ tên</th>
                    <th>Số điện thoại</th>
                    <th>Biển số xe</th>
                    <th>Trạng thái</th>
                    <th>Đơn hôm nay</th>
                    <th>Đánh giá</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @if(count($taiXes) > 0)
                @foreach ($taiXes as $tx)
                <tr>
                    <td class="text-muted">{{ $tx->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                style="width:34px;height:34px;font-size:0.8rem;flex-shrink:0;">
                                {{ strtoupper(mb_substr($tx->ho_ten, 0, 1)) }}
                            </div>
                            <strong>{{ $tx->ho_ten }}</strong>
                        </div>
                    </td>
                    <td>{{ $tx->so_dien_thoai ?? '—' }}</td>
                    <td>
                        @if($tx->bien_so_xe)
                            <span class="badge bg-light text-dark border" style="font-size:0.82rem;">{{ $tx->bien_so_xe }}</span>
                        @else —
                        @endif
                    </td>
                    <td><span class="status-badge {{ $tx->trang_thai_class }}">{{ $tx->trang_thai_label }}</span></td>
                    <td><strong>{{ $tx->so_don_hom_nay }}</strong> đơn</td>
                    <td>
                        <i class="bi bi-star-fill text-warning"></i>
                        <strong>{{ number_format($tx->danh_gia, 1) }}</strong>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.tai_xe.edit', $tx) }}"
                               class="btn btn-sm btn-outline-warning rounded-2" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.tai_xe.destroy', $tx) }}" method="POST"
                                  onsubmit="return confirm('Bạn có chắc muốn xoá tài xế này?')">
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
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-person-x fs-2 d-block mb-2"></i>
                        Không có tài xế nào. <a href="{{ route('admin.tai_xe.create') }}">Thêm ngay</a>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($taiXes->hasPages())
    <div class="px-4 py-3" style="border-top:1px solid #f1f5f9;">
        {{ $taiXes->links() }}
    </div>
    @endif
</div>

@endsection
