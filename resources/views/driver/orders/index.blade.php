@extends('layouts.driver')

@section('page_title', 'Đơn hàng của tôi')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">
            <span class="title-icon" style="background: linear-gradient(135deg, #4F8EF7, #667EEA);">
                <i class="bi bi-bag-check"></i>
            </span>
            Đơn hàng của tôi
        </h1>
        <p class="page-subtitle">Danh sách đơn hàng được phân công</p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="fs-sm text-muted">Tổng: <strong>{{ $donHangs->total() ?? count($donHangs ?? []) }}</strong> đơn</span>
    </div>
</div>

{{-- ── FILTER TABS ──────────────────────────────────── --}}
<!-- Quy ước ID DB: 1 (Chờ xử lý), 2 (Đã lấy hàng), 3 (Đang giao), 4 (Đã giao), 5 (Huỷ), 6 (Hoàn), 7 (Đã hoàn) -->
<div class="mb-4 order-filter-tabs d-flex gap-2 flex-wrap">
    <a href="{{ route('driver.orders') }}"
       class="filter-tab {{ !request('trang_thai') ? 'active' : '' }}">
        <i class="bi bi-grid-3x3-gap"></i> Tất cả
    </a>
    <a href="{{ route('driver.orders', ['trang_thai' => 2]) }}"
       class="filter-tab {{ request('trang_thai') == '2' ? 'active' : '' }}">
        <i class="bi bi-box-seam"></i> Đã lấy hàng
    </a>
    <a href="{{ route('driver.orders', ['trang_thai' => 3]) }}"
       class="filter-tab {{ request('trang_thai') == '3' ? 'active' : '' }}">
        <i class="bi bi-truck"></i> Đang giao
    </a>
    <a href="{{ route('driver.orders', ['trang_thai' => 4]) }}"
       class="filter-tab {{ request('trang_thai') == '4' ? 'active' : '' }}">
        <i class="bi bi-check2-circle"></i> Hoàn thành
    </a>
    <a href="{{ route('driver.orders', ['trang_thai' => 1]) }}"
       class="filter-tab {{ request('trang_thai') == '1' ? 'active' : '' }}">
        <i class="bi bi-hourglass-split"></i> Chờ xử lý
    </a>
    <a href="{{ route('driver.orders', ['trang_thai' => 5]) }}"
       class="filter-tab {{ request('trang_thai') == '5' ? 'active' : '' }}">
        <i class="bi bi-x-circle"></i> Huỷ/Thất bại
    </a>
    <a href="{{ route('driver.orders', ['trang_thai' => 6]) }}"
       class="filter-tab {{ request('trang_thai') == '6' ? 'active' : '' }}">
        <i class="bi bi-arrow-counterclockwise"></i> Hoàn hàng
    </a>
    <a href="{{ route('driver.orders', ['trang_thai' => 7]) }}"
       class="filter-tab {{ request('trang_thai') == '7' ? 'active' : '' }}">
        <i class="bi bi-archive"></i> Đã hoàn
    </a>
</div>

{{-- ── ORDERS TABLE ─────────────────────────────────── --}}
<div class="driver-card">
    <div class="driver-card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-table me-1" style="color:#4F8EF7;"></i> Danh sách đơn hàng</span>
        <a href="{{ route('driver.route') }}" class="btn btn-sm d-flex align-items-center gap-1"
           style="background:linear-gradient(135deg,#FF6B2B,#FF9A5C);color:#fff;border:none;border-radius:20px;padding:.35rem .9rem;font-size:.8rem;font-weight:600;">
            <i class="bi bi-map"></i> Xem bản đồ
        </a>
    </div>

    <div class="table-responsive d-none d-md-block">
        <table class="table driver-table mb-0">
            <thead>
                <tr>
                    <th style="width:130px;">Mã đơn</th>
                    <th>Khách hàng</th>
                    <th class="d-none d-lg-table-cell">Địa chỉ giao</th>
                    <th style="width:140px;">Trạng thái</th>
                    <th class="d-none d-md-table-cell" style="width:140px;">Thời gian</th>
                    <th style="width:160px;text-align:center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($donHangs ?? [] as $donHang)
                <tr class="order-row">
                    <td>
                        <span class="order-code">#{{ $donHang->ma_don ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <div class="customer-cell">
                            <div class="cust-avatar">
                                {{ strtoupper(substr($donHang->khachHang?->ten_khach ?? 'K', 0, 1)) }}
                            </div>
                            <div>
                                <div class="cust-name">{{ $donHang->khachHang?->ten_khach ?? '—' }}</div>
                                <div class="cust-phone">
                                    <i class="bi bi-telephone"></i>
                                    {{ $donHang->khachHang?->so_dien_thoai ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <div class="address-cell">
                            <i class="bi bi-geo-alt text-muted me-1"></i>
                            {{ Str::limit($donHang->khachHang?->dia_chi ?? '—', 45) }}
                        </div>
                    </td>
                    <td>
                        @php
                            $tenTT = $donHang->trangThai?->ten_trang_thai ?? '';
                            
                            $badge = match($donHang->trang_thai_id) {
                                1 => ['class' => 'badge-waiting',    'icon' => 'hourglass-split'],
                                2 => ['class' => 'badge-delivering', 'icon' => 'box-seam'],
                                3 => ['class' => 'badge-delivering', 'icon' => 'truck'],
                                4 => ['class' => 'badge-done',       'icon' => 'check2-circle'],
                                5 => ['class' => 'badge-cancelled',  'icon' => 'x-circle'],
                                6 => ['class' => 'badge-default',    'icon' => 'arrow-counterclockwise'],
                                7 => ['class' => 'badge-done',       'icon' => 'archive'],
                                default => ['class' => 'badge-default',    'icon' => 'circle'],
                            };
                        @endphp
                        <span class="status-badge-pill {{ $badge['class'] }}">
                            <i class="bi bi-{{ $badge['icon'] }}"></i>
                            {{ $tenTT ?: '—' }}
                        </span>
                    </td>

                    <td class="d-none d-md-table-cell">
                        <div style="font-size:.8rem;color:#718096;">
                            @if($donHang->thoi_gian_giao_du_kien)
                                <i class="bi bi-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($donHang->thoi_gian_giao_du_kien)->format('d/m H:i') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </td>
                    <td style="text-align:center;">
                        @php
                            $kh = $donHang->khachHang;
                            // Ưu tiên tọa độ GPS, fallback sang địa chỉ văn bản
                            if ($kh?->latitude && $kh?->longitude) {
                                $navUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . $kh->latitude . ',' . $kh->longitude;
                            } elseif ($kh?->dia_chi) {
                                $navUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($kh->dia_chi);
                            } else {
                                $navUrl = null;
                            }
                        @endphp
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="{{ route('driver.orders.show', $donHang->id) }}"
                               class="btn-detail" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            {{-- Nút Chỉ đường — luôn hiện nếu có địa chỉ (YC2) --}}
                            @if($navUrl)
                            <a href="{{ $navUrl }}" target="_blank"
                               class="btn-nav" title="Chỉ đường tới: {{ $kh?->dia_chi ?? '' }}">
                                <i class="bi bi-send-fill"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div style="color:#CBD5E0;">
                            <i class="bi bi-inbox display-4 d-block mb-2"></i>
                            <p style="font-size:.95rem;font-weight:500;margin:0;">Chưa có đơn hàng nào được phân công</p>
                            <p style="font-size:.8rem;margin:0;color:#A0AEC0;">Hệ thống sẽ thông báo khi có đơn mới</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Version --}}
    <div class="d-md-none p-3 pb-0" style="background:#F4F6FA;">
        @forelse($donHangs ?? [] as $donHang)
            <div class="driver-card mb-3 pb-2 pt-2 px-3 border-0 shadow-sm" style="border-radius:12px;">
                <div class="d-flex justify-content-between align-items-center mb-2 mt-1">
                    <span class="order-code">#{{ $donHang->ma_don ?? 'N/A' }}</span>
                    @php
                        $tenTT = $donHang->trangThai?->ten_trang_thai ?? '';
                        $badge = match(true) {
                            str_contains(strtolower($tenTT), 'giao') && !str_contains(strtolower($tenTT), 'thành')
                                                              => ['class' => 'badge-delivering', 'icon' => 'truck'],
                            str_contains(strtolower($tenTT), 'thành') => ['class' => 'badge-done',       'icon' => 'check2-circle'],
                            str_contains(strtolower($tenTT), 'chờ')   => ['class' => 'badge-waiting',    'icon' => 'hourglass-split'],
                            default                                    => ['class' => 'badge-default',    'icon' => 'circle'],
                        };
                    @endphp
                    <span class="status-badge-pill {{ $badge['class'] }}" style="font-size:.7rem;padding:.25rem .6rem;">
                        <i class="bi bi-{{ $badge['icon'] }}"></i> {{ $tenTT ?: '—' }}
                    </span>
                </div>

                <div class="customer-cell mb-2">
                    <div class="cust-avatar" style="width:34px;height:34px;">
                        {{ strtoupper(substr($donHang->khachHang?->ten_khach ?? 'K', 0, 1)) }}
                    </div>
                    <div>
                        <div class="cust-name" style="font-size:.85rem;">{{ $donHang->khachHang?->ten_khach ?? '—' }}</div>
                        <div class="cust-phone"><i class="bi bi-telephone"></i> {{ $donHang->khachHang?->so_dien_thoai ?? '—' }}</div>
                    </div>
                </div>

                <div class="address-cell mb-3" style="font-size:.8rem;max-width:100%;">
                    <i class="bi bi-geo-alt text-muted me-1"></i>
                    {{ Str::limit($donHang->khachHang?->dia_chi ?? '—', 50) }}
                </div>

                @php
                    $khM = $donHang->khachHang;
                    if ($khM?->latitude && $khM?->longitude) {
                        $navUrlM = 'https://www.google.com/maps/dir/?api=1&destination=' . $khM->latitude . ',' . $khM->longitude;
                    } elseif ($khM?->dia_chi) {
                        $navUrlM = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($khM->dia_chi);
                    } else {
                        $navUrlM = null;
                    }
                @endphp
                <div class="d-flex gap-2 mb-1">
                    <a href="{{ route('driver.orders.show', $donHang->id) }}"
                       class="btn btn-sm flex-fill d-flex justify-content-center align-items-center gap-1"
                       style="background:rgba(79,142,247,.1);color:#4F8EF7;border-radius:8px;font-weight:600;">
                        <i class="bi bi-eye"></i> Chi tiết
                    </a>
                    @if($navUrlM)
                    <a href="{{ $navUrlM }}" target="_blank"
                       class="btn btn-sm flex-fill d-flex justify-content-center align-items-center gap-1"
                       style="background:rgba(34,211,160,.1);color:#22d3a0;border-radius:8px;font-weight:600;">
                        <i class="bi bi-send-fill"></i> Chỉ đường
                    </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <div style="color:#CBD5E0;">
                    <i class="bi bi-inbox display-4 d-block mb-2"></i>
                    <p style="font-size:.9rem;font-weight:500;margin:0;">Chưa có đơn hàng nào</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(isset($donHangs) && method_exists($donHangs, 'links'))
    <div class="px-4 py-3 border-top">
        {{ $donHangs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection

@push('styles')
<style>
.order-filter-tabs { }
.filter-tab {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .45rem 1rem; border-radius: 20px;
    font-size: .82rem; font-weight: 500;
    text-decoration: none; color: #718096;
    background: #fff; border: 1px solid #E2E8F0; transition: all .2s;
}
.filter-tab:hover, .filter-tab.active {
    background: linear-gradient(135deg, #FF6B2B, #FF9A5C);
    color: #fff; border-color: #FF6B2B;
    box-shadow: 0 4px 12px rgba(255,107,43,.3);
}

.driver-table { border-collapse: separate; border-spacing: 0; }
.driver-table thead th {
    background: #F7FAFC; font-size: .75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px; color: #A0AEC0;
    border: none; padding: .9rem 1.25rem;
}
.driver-table tbody td {
    padding: 1rem 1.25rem; vertical-align: middle;
    border-top: 1px solid #F0F4F8; font-size: .88rem;
}
.order-row { transition: background .15s; }
.order-row:hover { background: #FAFBFF; }

.order-code {
    font-family: 'Courier New', monospace; font-weight: 700;
    font-size: .82rem; color: #4F8EF7;
    background: rgba(79,142,247,.08); padding: .25rem .65rem; border-radius: 6px;
}

.customer-cell { display: flex; align-items: center; gap: .75rem; }
.cust-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg, #667EEA, #764BA2);
    color: #fff; font-weight: 700; font-size: .85rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.cust-name  { font-size: .88rem; font-weight: 600; color: #2D3748; }
.cust-phone { font-size: .75rem; color: #A0AEC0; display: flex; align-items: center; gap: .3rem; }
.address-cell { font-size: .83rem; color: #718096; max-width: 300px; }

.status-badge-pill {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .75rem; font-weight: 600;
    padding: .3rem .8rem; border-radius: 20px; white-space: nowrap;
}
.badge-delivering { background: rgba(255,107,43,.1); color: #C2490D; border: 1px solid rgba(255,107,43,.2); }
.badge-done       { background: rgba(34,211,160,.1); color: #0C9B73; border: 1px solid rgba(34,211,160,.2); }
.badge-waiting    { background: rgba(79,142,247,.1); color: #2D6FBF; border: 1px solid rgba(79,142,247,.2); }
.badge-cancelled  { background: rgba(245,101,101,.1); color: #C53030; border: 1px solid rgba(245,101,101,.2); }
.badge-default    { background: #F0F4F8; color: #718096; border: 1px solid #E2E8F0; }

.btn-detail, .btn-nav {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 9px;
    text-decoration: none; font-size: .95rem; transition: all .2s;
}
.btn-detail { background: rgba(79,142,247,.1); color: #4F8EF7; }
.btn-detail:hover { background: #4F8EF7; color: #fff; box-shadow: 0 4px 12px rgba(79,142,247,.4); }

/* Nút Chỉ đường (YC2) */
.btn-nav { background: rgba(34,211,160,.1); color: #22d3a0; }
.btn-nav:hover { background: #22d3a0; color: #fff; box-shadow: 0 4px 12px rgba(34,211,160,.4); }
</style>
@endpush
