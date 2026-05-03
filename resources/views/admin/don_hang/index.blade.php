{{--
    TRANG QUẢN LÝ ĐƠN HÀNG — kết nối DB thật
--}}
@extends('layouts.admin')

@section('page_title', 'Quản lý đơn hàng')

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
        <h1 class="page-heading">Quản lý đơn hàng</h1>
        <p class="page-subtext">Danh sách toàn bộ đơn hàng trong hệ thống</p>
    </div>
    <a href="{{ route('admin.don_hang.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-1"></i> Thêm đơn hàng
    </a>
</div>

{{-- Bộ lọc nhanh theo trạng thái (load từ bảng trang_thai_don_hang) --}}
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('admin.don_hang.index', request()->except('trang_thai','page')) }}"
       class="btn btn-sm {{ !request('trang_thai') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-4">
        Tất cả
        <span class="badge {{ !request('trang_thai') ? 'bg-white text-primary' : 'bg-secondary' }} ms-1 rounded-pill">
            {{ $counts['tat_ca'] }}
        </span>
    </a>
    @foreach($trangThais as $tt)
    <a href="{{ route('admin.don_hang.index', array_merge(request()->except('trang_thai','page'), ['trang_thai' => $tt->id])) }}"
       class="btn btn-sm {{ request('trang_thai') == $tt->id ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-4">
        {{ $tt->ten_trang_thai }}
        <span class="badge {{ request('trang_thai') == $tt->id ? 'bg-white text-primary' : 'bg-secondary' }} ms-1 rounded-pill">
            {{ $counts[$tt->id] ?? 0 }}
        </span>
    </a>
    @endforeach
</div>

{{-- Tìm kiếm --}}
<form method="GET" action="{{ route('admin.don_hang.index') }}" class="mb-4">
    @if(request('trang_thai'))
        <input type="hidden" name="trang_thai" value="{{ request('trang_thai') }}">
    @endif
    <div class="input-group" style="max-width:400px;">
        <input type="text" name="search" class="form-control bg-light border-0 rounded-start-3"
               placeholder="Tìm mã đơn, khách hàng, địa chỉ..."
               value="{{ request('search') }}" style="font-size:0.88rem;">
        <button class="btn btn-primary rounded-end-3" type="submit"><i class="bi bi-search"></i></button>
        @if(request('search'))
            <a href="{{ route('admin.don_hang.index', request('trang_thai') ? ['trang_thai' => request('trang_thai')] : []) }}"
               class="btn btn-outline-secondary rounded-3 ms-2">
                <i class="bi bi-x"></i>
            </a>
        @endif
    </div>
</form>

<div class="data-table-wrapper">
    <div class="table-header">
        <h5><i class="bi bi-box-seam me-2 text-primary"></i>Danh sách đơn hàng
            <span class="badge bg-primary ms-1">{{ $donHangs->total() }}</span>
        </h5>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tài xế</th>
                    <th>Địa chỉ giao</th>
                    <th>Trạng thái</th>
                    <th>Thời gian tạo</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @if(count($donHangs) > 0)
                @foreach ($donHangs as $dh)
                <tr>
                    <td>
                        <strong>{{ $dh->ma_don }}</strong>
                        @if($dh->delivery_photo)
                            <i class="bi bi-camera-fill text-primary ms-1" title="Có ảnh xác nhận giao hàng"></i>
                        @endif
                    </td>
                    <td>{{ $dh->khachHang->ten_khach ?? '—' }}</td>
                    <td>
                        @if($dh->tai_xe_id)
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-medium text-dark">{{ $dh->taiXe->ho_ten ?? '—' }}</span>
                                <button type="button" class="btn btn-sm btn-light py-0 px-2 rounded-2 border" 
                                        onclick="openAssignModal({{ $dh->id }}, '{{ $dh->tai_xe_id }}')" 
                                        title="Đổi tài xế">
                                    <i class="bi bi-pencil" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                        @else
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1"
                                    onclick="openAssignModal({{ $dh->id }}, '')" style="font-size: 0.8rem;">
                                <i class="bi bi-person-plus me-1"></i> Gán tài xế
                            </button>
                        @endif
                    </td>
                    <td style="max-width:200px; font-size:0.85rem; color:#64748b;">{{ $dh->dia_chi_giao }}</td>
                    <td><span class="status-badge {{ $dh->trangThai?->css_class ?? '' }}">{{ $dh->trangThai?->ten_trang_thai ?? '—' }}</span></td>
                    <td style="font-size:0.85rem; color:#64748b;">{{ $dh->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.don_hang.show', $dh) }}"
                               class="btn btn-sm btn-outline-primary rounded-2" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('admin.don_hang.edit', $dh) }}"
                               class="btn btn-sm btn-outline-warning rounded-2" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.don_hang.destroy', $dh) }}" method="POST"
                                  onsubmit="return confirm('Bạn có chắc muốn xoá đơn hàng {{ $dh->ma_don }}?')">
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
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        Không có đơn hàng nào. <a href="{{ route('admin.don_hang.create') }}">Tạo đơn đầu tiên</a>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($donHangs->hasPages())
    <div class="d-flex justify-content-between align-items-center px-4 py-3" style="border-top:1px solid #f1f5f9;">
        <span class="text-muted" style="font-size:0.82rem;">
            Hiển thị {{ $donHangs->firstItem() }}–{{ $donHangs->lastItem() }} / {{ $donHangs->total() }} đơn hàng
        </span>
        {{ $donHangs->links() }}
    </div>
    @endif

</div>

<!-- Modal Gán tài xế -->
<div class="modal fade" id="assignDriverModal" tabindex="-1" aria-labelledby="assignDriverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="assignDriverForm" method="POST" action="">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title" id="assignDriverModalLabel">Gán tài xế cho đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tai_xe_id" class="form-label fw-600">Chọn tài xế <span class="text-danger">*</span></label>
                        <select class="form-select" id="tai_xe_id" name="tai_xe_id" required>
                            <option value="">— Chọn tài xế —</option>
                            @if(isset($taiXes))
                                @foreach($taiXes as $tx)
                                    <option value="{{ $tx->id }}">{{ $tx->ho_ten }} - {{ $tx->so_dien_thoai }} ({{ $tx->trang_thai_label }})</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4">Xác nhận</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openAssignModal(orderId, currentDriverId) {
    const form = document.getElementById('assignDriverForm');
    form.action = `/admin/don-hang/${orderId}/assign-driver`;
    
    const select = document.getElementById('tai_xe_id');
    select.value = currentDriverId || '';
    
    const modal = new bootstrap.Modal(document.getElementById('assignDriverModal'));
    modal.show();
}
</script>

@endsection
