@extends('layouts.shop')
@section('page_title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-800 fs-4 mb-1">
            <i class="bi bi-speedometer2" style="color:#6C63FF;"></i>
            Dashboard
        </h1>
        <p class="text-muted mb-0">
            Xin chào, <strong>{{ $shop?->ten_shop ?? Auth::user()->name }}</strong>!
            · {{ now()->locale('vi')->isoFormat('dddd, D/M/YYYY') }}
        </p>
    </div>
    <a href="{{ route('shop.don_hang.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tạo đơn mới
    </a>
</div>

{{-- STAT CARDS --}}
<div class="row g-4 mb-4">
    {{-- Tổng đơn --}}
    <div class="col-xl-3 col-md-6">
        <div class="bg-white rounded-4 p-4 shadow-sm d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:56px;height:56px;background:linear-gradient(135deg,#6C63FF,#A29BFE);">
                <i class="bi bi-box-seam text-white fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-600">Tổng đơn</div>
                <div class="fw-800 fs-3 lh-1">{{ $tongDon }}</div>
            </div>
        </div>
    </div>

    {{-- Đang giao --}}
    <div class="col-xl-3 col-md-6">
        <div class="bg-white rounded-4 p-4 shadow-sm d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:56px;height:56px;background:linear-gradient(135deg,#FF6B2B,#FF9A5C);">
                <i class="bi bi-truck text-white fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-600">Đang giao</div>
                <div class="fw-800 fs-3 lh-1">{{ $dangGiao }}</div>
            </div>
        </div>
    </div>

    {{-- Hoàn thành --}}
    <div class="col-xl-3 col-md-6">
        <div class="bg-white rounded-4 p-4 shadow-sm d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:56px;height:56px;background:linear-gradient(135deg,#22d3a0,#0d9488);">
                <i class="bi bi-check2-circle text-white fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-600">Hoàn thành</div>
                <div class="fw-800 fs-3 lh-1">{{ $daHoanThanh }}</div>
            </div>
        </div>
    </div>

    {{-- COD chờ đối soát --}}
    <div class="col-xl-3 col-md-6">
        <div class="bg-white rounded-4 p-4 shadow-sm d-flex align-items-center gap-3 border border-2"
             style="border-color:{{ $codBalance >= 0 ? '#6C63FF' : '#dc3545' }} !important;">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:56px;height:56px;background:{{ $codBalance >= 0 ? 'linear-gradient(135deg,#6C63FF,#A29BFE)' : 'linear-gradient(135deg,#dc3545,#f87171)' }};">
                <i class="bi bi-safe2 text-white fs-4"></i>
            </div>
            <div>
                <div class="text-muted small fw-600">COD chờ đối soát</div>
                <div class="fw-800 fs-4 lh-1 {{ $codBalance >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format(abs($codBalance), 0, ',', '.') }}đ
                </div>
                <span class="badge {{ $codBalance >= 0 ? 'bg-success' : 'bg-danger' }} rounded-pill" style="font-size:.7rem;">
                    {{ $codBalance >= 0 ? 'Đang chờ nhận' : 'Đang nợ' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Recent Orders --}}
<div class="bg-white rounded-4 shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-700"><i class="bi bi-clock-history me-2 text-primary"></i>Đơn hàng gần nhất</span>
        <a href="{{ route('shop.don_hang.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Người nhận</th>
                    <th>COD</th>
                    <th>Phí ship</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $dh)
                <tr>
                    <td class="fw-600">{{ $dh->ma_don }}</td>
                    <td>{{ $dh->khachHang?->ten_khach }}</td>
                    <td>{{ number_format($dh->cod_amount, 0, ',', '.') }}đ</td>
                    <td>{{ number_format($dh->shipping_fee, 0, ',', '.') }}đ</td>
                    <td>
                        <span class="badge rounded-pill" style="background:#EDE9FE;color:#6C63FF;">
                            {{ $dh->trangThai?->ten_trang_thai ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('shop.don_hang.show', $dh->id) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Chưa có đơn hàng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
