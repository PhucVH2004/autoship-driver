@extends('layouts.driver')
@section('page_title', 'Lộ trình hôm nay')

{{-- ══════════════════════════════════════════════════════════════════════
     DRIVER ROUTE — today.blade.php
     Tính năng:
       1. Bản đồ Leaflet + OpenStreetMap (markers + polyline)
       2. Sidebar danh sách điểm giao (sắp xếp Nearest Neighbor)
       3. Server-side optimize (POST /driver/route/optimize)
       4. Realtime GPS tracking (update-position mỗi 15s)
       5. Cập nhật trạng thái đơn ngay trên map (AJAX)
       6. Haversine ước tính km tổng tuyến đường
       7. Chỉ đường Google Maps (GPS hoặc fallback địa chỉ)
══════════════════════════════════════════════════════════════════════ --}}

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
/* ── Layout ─────────────────────────────────────────────── */
.page-header         { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.25rem; gap:1rem; flex-wrap:wrap; }
.page-title          { font-size:1.25rem; font-weight:800; color:#1A202C; display:flex; align-items:center; gap:.6rem; margin:0; }
.title-icon          { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1rem; flex-shrink:0; }
.page-subtitle       { font-size:.8rem; color:#A0AEC0; margin:.3rem 0 0 46px; }

.route-action-btn {
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.48rem 1rem; background:#fff;
    border:1px solid #E2E8F0; border-radius:20px;
    color:#4A5568; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .2s;
}
.route-action-btn:hover { background:linear-gradient(135deg,#FF6B2B,#FF9A5C); color:#fff; border-color:#FF6B2B; box-shadow:0 4px 12px rgba(255,107,43,.3); }
.route-action-btn:disabled { opacity:.55; cursor:not-allowed; }

/* ── Stat cards ────────────────────────────────────────── */
.driver-card { background:#fff; border:1px solid #E2E8F0; border-radius:16px; box-shadow:0 2px 12px rgba(0,0,0,.04); }
.driver-card-body { padding:1rem 1.1rem; }
.next-stop-card { overflow:hidden; }
.next-stop-card .driver-card-body { display:flex; justify-content:space-between; align-items:flex-start; gap:1rem; flex-wrap:wrap; }
.next-stop-main { display:flex; gap:.9rem; align-items:flex-start; }
.next-stop-icon { width:44px; height:44px; border-radius:12px; background:linear-gradient(135deg,#FF6B2B,#F59E0B); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; box-shadow:0 8px 18px rgba(245,158,11,.25); }
.next-stop-title { font-size:.76rem; text-transform:uppercase; letter-spacing:.04em; color:#F97316; font-weight:800; margin-bottom:.15rem; }
.next-stop-name { font-size:1rem; font-weight:800; color:#1A202C; }
.next-stop-meta { display:flex; flex-wrap:wrap; gap:.45rem; margin-top:.45rem; }
.next-stop-chip { display:inline-flex; align-items:center; gap:.35rem; padding:.28rem .62rem; border-radius:999px; background:#F7FAFC; border:1px solid #E2E8F0; font-size:.72rem; color:#4A5568; font-weight:600; }
.next-stop-actions { display:flex; flex-wrap:wrap; gap:.55rem; }
.route-mini-card { background:#fff; border-radius:14px; border:1px solid #E2E8F0; padding:.9rem 1rem; display:flex; align-items:center; gap:.8rem; box-shadow:0 2px 8px rgba(0,0,0,.04); }
.rmc-icon { width:40px; height:40px; border-radius:10px; background:color-mix(in srgb,var(--c) 13%,transparent); color:var(--c); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.rmc-val  { font-size:1.45rem; font-weight:800; color:#1A202C; line-height:1; }
.rmc-label{ font-size:.7rem; color:#A0AEC0; font-weight:500; margin-top:.15rem; }

/* ── Main layout ───────────────────────────────────────── */
.route-wrapper { display:flex; gap:1.1rem; height:620px; }

/* ── Sidebar ───────────────────────────────────────────── */
.route-orders-panel  { width:295px; flex-shrink:0; background:#fff; border-radius:16px; border:1px solid #E2E8F0; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.04); }
.rop-header          { padding:.85rem 1rem; border-bottom:1px solid #F0F4F8; font-size:.83rem; font-weight:700; color:#2D3748; display:flex; align-items:center; gap:.5rem; }
.rop-body            { flex:1; overflow-y:auto; padding:.45rem; }
.rop-body::-webkit-scrollbar { width:3px; }
.rop-body::-webkit-scrollbar-thumb { background:#E2E8F0; border-radius:2px; }

.rop-item { display:flex; align-items:flex-start; gap:.6rem; padding:.7rem .55rem; border-radius:10px; cursor:pointer; transition:all .15s; margin-bottom:.3rem; border:1px solid transparent; }
.rop-item:hover { background:#F7FAFC; border-color:#E2E8F0; }
.rop-item.done  { opacity:.55; }
.rop-item.active{ background:#EEF4FF; border-color:#4F8EF7; }
.rop-item.next-stop { background:rgba(255,107,43,.08); border-color:rgba(255,107,43,.3); }
.rop-item.next-stop.active { background:rgba(255,107,43,.14); border-color:#FF6B2B; }

.rop-num      { width:24px; height:24px; border-radius:50%; background:#4F8EF7; color:#fff; font-size:.7rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:.12rem; }
.rop-num.done { background:#22d3a0; }
.rop-num.next-stop { background:#FF6B2B; box-shadow:0 0 0 3px rgba(255,107,43,.15); }

.rop-info  { flex:1; min-width:0; }
.rop-name  { font-size:.82rem; font-weight:600; color:#2D3748; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.rop-addr  { font-size:.72rem; color:#A0AEC0; margin-top:.08rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.rop-cod   { font-size:.7rem; font-weight:600; color:#0C9B73; margin-top:.1rem; }
.rop-status{ font-size:.68rem; font-weight:600; display:inline-flex; align-items:center; gap:.3rem; margin-top:.25rem; padding:.12rem .45rem; border-radius:20px; }
.rop-status.done   { background:rgba(34,211,160,.1); color:#0C9B73; }
.rop-status.pending{ background:rgba(255,107,43,.1); color:#C2490D; }
.rop-status.next-stop { background:rgba(79,142,247,.12); color:#2B6CB0; }
.rop-next-label { font-size:.66rem; font-weight:800; color:#FF6B2B; text-transform:uppercase; letter-spacing:.03em; margin-top:.2rem; }
.rop-meta { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.25rem; }
.rop-badge { display:inline-flex; align-items:center; gap:.25rem; border-radius:999px; padding:.12rem .45rem; background:#F7FAFC; color:#4A5568; border:1px solid #E2E8F0; font-size:.66rem; font-weight:600; }
.rop-badge.muted { color:#A0AEC0; }
.rop-badge.warn { background:rgba(255,107,43,.08); color:#C05621; border-color:rgba(255,107,43,.2); }
.rop-badge.success { background:rgba(34,211,160,.08); color:#0C9B73; border-color:rgba(34,211,160,.18); }
.text-orange { color:#FF6B2B; }

.rop-eyebtn, .rop-navbtn {
    width:26px; height:26px; border-radius:7px;
    display:flex; align-items:center; justify-content:center;
    text-decoration:none; font-size:.78rem; flex-shrink:0; transition:all .2s;
}
.rop-eyebtn { background:rgba(79,142,247,.1); color:#4F8EF7; }
.rop-eyebtn:hover { background:#4F8EF7; color:#fff; }
.rop-navbtn { background:rgba(34,211,160,.1); color:#22d3a0; }
.rop-navbtn:hover { background:#22d3a0; color:#fff; }

/* ── Map ───────────────────────────────────────────────── */
.route-map-wrap { flex:1; border-radius:16px; overflow:hidden; position:relative; border:1px solid #E2E8F0; box-shadow:0 2px 12px rgba(0,0,0,.06); }
#deliveryMap    { width:100%; height:100%; min-height:620px; }

.map-legend { position:absolute; bottom:1.1rem; right:1.1rem; background:rgba(26,29,46,.88); backdrop-filter:blur(6px); border-radius:10px; padding:.6rem .85rem; z-index:500; display:flex; flex-direction:column; gap:.35rem; }
.legend-item{ display:flex; align-items:center; gap:.45rem; font-size:.72rem; color:rgba(255,255,255,.82); }
.legend-dot { width:9px; height:9px; border-radius:50%; display:inline-block; flex-shrink:0; }

/* ── Floating controls on map ──────────────────────────── */
.map-fab {
    position:absolute; z-index:500; border:none; border-radius:22px; cursor:pointer;
    font-size:.78rem; font-weight:600; display:flex; align-items:center; gap:.4rem;
    padding:.5rem 1rem; box-shadow:0 4px 14px rgba(0,0,0,.18); transition:all .2s;
}
.map-fab:hover { transform:translateY(-1px); }
#optimizeBtn { top:1rem; right:1rem; background:linear-gradient(135deg,#A78BFA,#7C3AED); color:#fff; box-shadow:0 4px 14px rgba(124,58,237,.4); }
#trackBtn    { top:1rem; right:9rem; background:linear-gradient(135deg,#22d3a0,#059669); color:#fff; box-shadow:0 4px 14px rgba(5,150,105,.4); }

/* ── GPS tracking status dot ───────────────────────────── */
#trackDot { width:8px; height:8px; border-radius:50%; background:#E53E3E; display:inline-block; margin-left:.3rem; }
#trackDot.active { background:#22d3a0; animation:blink 1.4s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

/* ── Quick-status bottom sheet (slides up on marker click) */
#statusSheet {
    position:absolute; bottom:0; left:0; right:0; z-index:600;
    background:#fff; border-radius:16px 16px 0 0; padding:1.1rem 1.25rem 1.4rem;
    box-shadow:0 -6px 24px rgba(0,0,0,.14);
    transform:translateY(100%); transition:transform .3s ease;
}
#statusSheet.open { transform:translateY(0); }
.sheet-handle { width:36px; height:4px; background:#E2E8F0; border-radius:2px; margin:0 auto .85rem; }
#sheetTitle   { font-size:.95rem; font-weight:700; color:#1A202C; margin-bottom:.65rem; }
.sheet-btn    { flex:1; padding:.55rem; border-radius:10px; border:none; font-size:.8rem; font-weight:600; cursor:pointer; transition:all .2s; }
.sheet-btn.confirm  { background:linear-gradient(135deg,#22d3a0,#059669); color:#fff; }
.sheet-btn.fail     { background:linear-gradient(135deg,#FC8181,#E53E3E); color:#fff; }
.sheet-btn.cancel   { background:#F7FAFC; color:#4A5568; border:1px solid #E2E8F0; }
.sheet-btn:hover    { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.12); }

/* ── Responsive ────────────────────────────────────────── */
@media (max-width:768px) {
    .route-wrapper { flex-direction:column; height:auto; }
    .route-orders-panel { width:100%; height:260px; }
    #deliveryMap { min-height:420px; }
}

/* Leaflet routing panel ẩn */
.leaflet-routing-container { display:none !important; }

/* Pulse animation cho vị trí tôi */
@keyframes pulse-dot { 0%,100%{box-shadow:0 0 0 0 rgba(255,107,43,.6)} 50%{box-shadow:0 0 0 10px rgba(255,107,43,0)} }
</style>
@endpush

@section('content')

{{-- ── PAGE HEADER ────────────────────────────────────────────────────── --}}
<div class="page-header">
    <div>
        <h1 class="page-title">
            <span class="title-icon" style="background:linear-gradient(135deg,#22d3a0,#0EA5E9);">
                <i class="bi bi-map"></i>
            </span>
            Lộ trình hôm nay
        </h1>
        <p class="page-subtitle">
            <i class="bi bi-pin-map me-1"></i>
            Bản đồ điểm giao &amp; tối ưu Nearest Neighbor —
            {{ now()->format('d/m/Y') }}
            @if($optimizedResult)
                &nbsp;·&nbsp;<span style="color:#22d3a0;font-weight:600;">
                    <i class="bi bi-magic me-1"></i>Đã tối ưu từ vị trí GPS
                </span>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="route-action-btn" onclick="locateAndOptimize()" id="locateBtn">
            <i class="bi bi-crosshair2"></i> Vị trí &amp; Lộ trình
        </button>
        <button class="route-action-btn" onclick="fitAllMarkers()">
            <i class="bi bi-fullscreen"></i> Toàn lộ trình
        </button>
    </div>
</div>

@if($nextStop)
<div class="driver-card next-stop-card mb-4">
    <div class="driver-card-body">
        <div class="next-stop-main">
            <div class="next-stop-icon">
                <i class="bi bi-signpost-split"></i>
            </div>
            <div>
                <div class="next-stop-title">Điểm tiếp theo</div>
                <div class="next-stop-name">{{ $nextStop->ma_don }} — {{ $nextStop->khachHang?->ten_khach ?? 'Khách hàng' }}</div>
                <div class="text-muted" style="font-size:.82rem; margin-top:.2rem;">
                    <i class="bi bi-geo-alt me-1 text-orange"></i>
                    {{ $nextStop->khachHang?->dia_chi ?? 'Chưa có địa chỉ' }}
                </div>
                <div class="next-stop-meta">
                    <span class="next-stop-chip">
                        <i class="bi bi-info-circle"></i>
                        {{ $nextStop->trangThai?->ten_trang_thai ?? 'Chờ giao' }}
                    </span>
                    @if(($nextStop->cod_amount ?? 0) > 0)
                    <span class="next-stop-chip">
                        <i class="bi bi-cash"></i>
                        COD {{ number_format($nextStop->cod_amount, 0, ',', '.') }} đ
                    </span>
                    @endif
                    <span class="next-stop-chip">
                        <i class="bi bi-list-ol"></i>
                        Thứ tự {{ collect($deliveries)->search(fn ($delivery) => $delivery['id'] === $nextStop->id) + 1 }}
                    </span>
                </div>
            </div>
        </div>
        <div class="next-stop-actions">
            <a href="{{ route('driver.orders.show', $nextStop->id) }}" class="route-action-btn">
                <i class="bi bi-eye"></i> Xem đơn
            </a>
            @php
                $nextLat = (float) ($nextStop->khachHang?->latitude ?? 0);
                $nextLng = (float) ($nextStop->khachHang?->longitude ?? 0);
                $nextAddr = $nextStop->khachHang?->dia_chi;
                $nextNavUrl = ($nextLat && $nextLng)
                    ? "https://www.google.com/maps/dir/?api=1&destination={$nextLat},{$nextLng}"
                    : ($nextAddr ? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($nextAddr) : null);
            @endphp
            @if($nextNavUrl)
            <a href="{{ $nextNavUrl }}" target="_blank" class="route-action-btn">
                <i class="bi bi-send"></i> Chỉ đường
            </a>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── STAT CARDS ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="route-mini-card" style="--c:#4F8EF7;">
            <div class="rmc-icon"><i class="bi bi-geo-alt-fill"></i></div>
            <div>
                <div class="rmc-val" id="countDeliveries">{{ $routeSummary['total'] }}</div>
                <div class="rmc-label">Tổng điểm giao</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="route-mini-card" style="--c:#22d3a0;">
            <div class="rmc-icon"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="rmc-val" id="completedDeliveries">{{ $routeSummary['completed'] }}</div>
                <div class="rmc-label">Đã hoàn thành</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="route-mini-card" style="--c:#FF6B2B;">
            <div class="rmc-icon"><i class="bi bi-truck"></i></div>
            <div>
                <div class="rmc-val" id="remainingDeliveries">{{ $routeSummary['remaining'] }}</div>
                <div class="rmc-label">Cần xử lý tiếp</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="route-mini-card" style="--c:#A78BFA;">
            <div class="rmc-icon"><i class="bi bi-signpost-2"></i></div>
            <div>
                <div class="rmc-val" id="estKm">
                    {{ $optimizedResult ? $optimizedResult['total_km'] . ' km' : ((float) ($session?->total_km ?? 0) > 0 ? number_format((float) $session->total_km, 1) . ' km' : '—') }}
                </div>
                <div class="rmc-label">Ước tính km</div>
            </div>
        </div>
    </div>
</div>

@if($routeSummary['failed'] > 0)
<div class="mb-4">
    <span class="rop-badge warn">
        <i class="bi bi-exclamation-circle"></i>
        Có {{ $routeSummary['failed'] }} đơn không thành công trong tuyến hôm nay
    </span>
</div>
@endif

@if($session)
<div class="mb-4">
    <span class="rop-badge {{ $session->status === 'completed' ? 'success' : '' }}">
        <i class="bi bi-diagram-3"></i>
        Phiên lộ trình: {{ $session->displayStatus() }}
    </span>
</div>
@endif

@if(!$nextStop && count($deliveries) > 0)
<div class="driver-card mb-4">
    <div class="driver-card-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <div class="next-stop-title mb-1">Tuyến hôm nay</div>
            <div class="fw-bold text-dark">Bạn đã xử lý hết các điểm trong danh sách hiện tại.</div>
        </div>
        <button class="route-action-btn" onclick="fitAllMarkers()">
            <i class="bi bi-fullscreen"></i> Xem toàn tuyến
        </button>
    </div>
</div>
@endif

@if(count($deliveries) === 0)
<div class="driver-card mb-4">
    <div class="driver-card-body text-muted" style="font-size:.9rem;">
        <i class="bi bi-info-circle me-1"></i>
        Hôm nay chưa có điểm giao nào để hiển thị trên lộ trình.
    </div>
</div>
@endif

@if($nextStop)
@php
    $nextStopIndex = collect($deliveries)->search(fn ($delivery) => $delivery['id'] === $nextStop->id);
@endphp
@endif

@if(!$nextStop)
@php($nextStopIndex = false)
@endif

@if($nextStopIndex !== false)
<input type="hidden" id="nextStopIndex" value="{{ $nextStopIndex }}">
@endif

@if($routeSummary['remaining'] > 0)
<div class="mb-3 text-muted" style="font-size:.8rem;">
    <i class="bi bi-lightning-charge me-1 text-orange"></i>
    Ưu tiên xem mục được đánh dấu <strong>Điểm tiếp theo</strong> để biết ngay đơn nên xử lý trước.
</div>
@endif


{{-- ── MAP + SIDEBAR ───────────────────────────────────────────────────── --}}
<div class="route-wrapper">

    {{-- Sidebar danh sách điểm giao --}}
    <div class="route-orders-panel">
        <div class="rop-header">
            <i class="bi bi-list-check"></i>
            Danh sách điểm giao
            <span id="optimizedBadge"
                  class="ms-auto badge"
                  style="{{ $optimizedResult ? '' : 'display:none;' }} background:rgba(167,139,250,.15);color:#7C3AED;border-radius:20px;font-size:.66rem;padding:.2rem .55rem;">
                <i class="bi bi-magic"></i> Đã tối ưu
            </span>
        </div>

        <div class="rop-body" id="routeOrderList">
            @if(count($deliveries) === 0)
            <div class="text-center p-4" style="color:#A0AEC0;">
                <i class="bi bi-map-fill d-block fs-2 mb-2"></i>
                <p style="font-size:.83rem;">Chưa có đơn nào hôm nay</p>
            </div>
            @else
            <div id="orderListItems">
                @foreach($deliveries as $i => $d)
                <div class="rop-item {{ $d['is_done'] ? 'done' : '' }} {{ $d['is_next'] ? 'next-stop' : '' }}"
                     id="rop-{{ $i }}"
                     onclick="focusDelivery({{ $i }})">
                    <div class="rop-num {{ $d['is_done'] ? 'done' : '' }} {{ $d['is_next'] ? 'next-stop' : '' }}">{{ $i + 1 }}</div>
                    <div class="rop-info">
                        @if($d['is_next'])
                        <div class="rop-next-label">
                            <i class="bi bi-lightning-charge-fill me-1"></i>Điểm tiếp theo
                        </div>
                        @endif
                        <div class="rop-name">{{ $d['ten_khach'] }}</div>
                        <div class="rop-addr">
                            <i class="bi bi-geo-alt"></i>
                            {{ Str::limit($d['dia_chi'], 36) }}
                        </div>
                        <div class="rop-meta">
                            <span class="rop-status {{ $d['is_done'] ? 'done' : ($d['is_next'] ? 'next-stop' : 'pending') }}">
                                <i class="bi bi-{{ $d['is_done'] ? 'check2-circle' : ($d['is_next'] ? 'signpost-split' : 'clock') }}"></i>
                                {{ $d['trang_thai'] }}
                            </span>
                            @if(!$d['has_coords'])
                            <span class="rop-badge muted">
                                <i class="bi bi-exclamation-triangle"></i>Chưa có tọa độ
                            </span>
                            @endif
                        </div>
                        @if($d['cod_amount'] > 0)
                        <div class="rop-cod">
                            <i class="bi bi-cash me-1"></i>COD: {{ number_format($d['cod_amount']) }}đ
                        </div>
                        @endif
                    </div>
                    <div class="d-flex flex-column gap-1">
                        @if($d['nav_url'])
                        <a href="{{ $d['nav_url'] }}" target="_blank"
                           class="rop-navbtn" title="Chỉ đường"
                           onclick="event.stopPropagation()">
                            <i class="bi bi-send-fill"></i>
                        </a>
                        @endif
                        <a href="{{ route('driver.orders.show', $d['id']) }}"
                           class="rop-eyebtn" title="Chi tiết"
                           onclick="event.stopPropagation()">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Bản đồ --}}
    <div class="route-map-wrap">
        <div id="deliveryMap"></div>

        {{-- Floating buttons --}}
        <button id="optimizeBtn" class="map-fab" onclick="serverOptimize()">
            <i class="bi bi-magic"></i> Tối ưu lộ trình
        </button>
        <button id="trackBtn" class="map-fab" onclick="toggleTracking()">
            <i class="bi bi-broadcast"></i> Theo dõi GPS
            <span id="trackDot"></span>
        </button>

        {{-- Legend --}}
        <div class="map-legend">
            <div class="legend-item"><span class="legend-dot" style="background:#4F8EF7;"></span> Chưa giao</div>
            <div class="legend-item"><span class="legend-dot" style="background:#22d3a0;"></span> Đã hoàn thành</div>
            <div class="legend-item"><span class="legend-dot" style="background:#FC8181;"></span> Đang giao</div>
            <div class="legend-item"><span class="legend-dot" style="background:#1A1D2E;box-shadow:0 0 0 2px #FF6B2B;"></span> Vị trí tôi</div>
        </div>

        {{-- Quick-status bottom sheet --}}
        <div id="statusSheet">
            <div class="sheet-handle"></div>
            <div id="sheetTitle">Đơn hàng #—</div>
            <div id="sheetAddr" style="font-size:.82rem;color:#718096;margin-bottom:.85rem;"></div>
            <div class="d-flex gap-2">
                <button class="sheet-btn confirm" onclick="submitStatus('confirm')">
                    <i class="bi bi-check2-circle me-1"></i>Đã giao xong
                </button>
                <button class="sheet-btn fail" onclick="submitStatus('fail')">
                    <i class="bi bi-x-circle me-1"></i>Giao thất bại
                </button>
                <button class="sheet-btn cancel" onclick="closeSheet()">Đóng</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ══════════════════════════════════════════════════════════════════
//  DATA từ Blade
// ══════════════════════════════════════════════════════════════════
let deliveries  = @json($deliveries);
const CSRF      = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const OPTIMIZE_URL       = "{{ route('driver.route.optimize') }}";
const UPDATE_POS_URL     = "{{ route('driver.route.update_position') }}";
const HOAN_THANH_ID      = {{ \App\Models\TrangThaiDonHang::HOAN_THANH }};
const HUY_ID             = {{ \App\Models\TrangThaiDonHang::HUY }};

// Vị trí GPS tài xế (từ DB nếu có)
let driverLat = {{ $taiXeLat ?: 'null' }};
let driverLng = {{ $taiXeLng ?: 'null' }};

// ══════════════════════════════════════════════════════════════════
//  LEAFLET MAP INIT
// ══════════════════════════════════════════════════════════════════
const defaultCenter = driverLat && driverLng ? [driverLat, driverLng] : [10.7769, 106.7009];
const map = L.map('deliveryMap', { center: defaultCenter, zoom: 13, zoomControl: true });

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
    maxZoom: 19,
}).addTo(map);

// ══════════════════════════════════════════════════════════════════
//  ICONS
// ══════════════════════════════════════════════════════════════════
function makeIcon(color, number, size = 34) {
    const label = number !== null
        ? `<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.6rem;font-weight:800;transform:rotate(45deg);">${number}</div>`
        : '';
    return L.divIcon({
        className: '',
        html: `<div style="position:relative;width:${size}px;height:${size}px;border-radius:50% 50% 50% 0;background:${color};border:2.5px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,.35);transform:rotate(-45deg);">${label}</div>`,
        iconSize: [size, size], iconAnchor: [size/2, size], popupAnchor: [0, -size],
    });
}

function myLocIcon() {
    return L.divIcon({
        className: '',
        html: `<div style="width:16px;height:16px;border-radius:50%;background:#1A1D2E;border:2.5px solid #FF6B2B;box-shadow:0 0 0 6px rgba(255,107,43,.2);animation:pulse-dot 2s infinite;"></div>`,
        iconSize: [16,16], iconAnchor: [8,8],
    });
}

// ══════════════════════════════════════════════════════════════════
//  MARKERS
// ══════════════════════════════════════════════════════════════════
const markerObjects = [];  // { marker, d, idx }
let myLocMarker = null;
let polyline    = null;

function addMarkers(list) {
    // Xoá marker cũ
    markerObjects.forEach(m => map.removeLayer(m.marker));
    markerObjects.length = 0;

    list.forEach((d, idx) => {
        if (!d.lat || !d.lng) return;

        const color  = d.is_done ? '#22d3a0' : (d.is_next ? '#FF6B2B' : '#4F8EF7');
        const marker = L.marker([d.lat, d.lng], { icon: makeIcon(color, idx + 1) })
            .bindPopup(buildPopup(d, idx), { maxWidth: 280 })
            .addTo(map);

        marker.on('click', () => openSheet(d));
        markerObjects.push({ marker, d, idx });
    });
}

function buildPopup(d, idx) {
    const navLink = d.lat && d.lng
        ? `https://www.google.com/maps/dir/?api=1&destination=${d.lat},${d.lng}`
        : (d.dia_chi !== '—' ? `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(d.dia_chi)}` : '#');

    const badgeBg = d.is_done
        ? 'rgba(34,211,160,.12)'
        : (d.is_next ? 'rgba(79,142,247,.12)' : 'rgba(255,107,43,.12)');
    const badgeColor = d.is_done
        ? '#0C9B73'
        : (d.is_next ? '#2B6CB0' : '#C2490D');
    const badgeIcon = d.is_done ? '✓' : (d.is_next ? '➜' : '⏳');
    const nextLabel = d.is_next
        ? `<div style="font-size:.68rem;font-weight:800;color:#FF6B2B;text-transform:uppercase;letter-spacing:.04em;margin-bottom:.28rem;">Điểm tiếp theo</div>`
        : '';

    return `<div style="min-width:200px;font-family:'Inter',sans-serif;">
        ${nextLabel}
        <div style="font-weight:700;font-size:.9rem;margin-bottom:.3rem;color:#1A202C;">
            <span style="background:${d.is_next ? '#FF6B2B' : '#4F8EF7'};color:#fff;border-radius:50%;width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;margin-right:.35rem;">${idx+1}</span>
            ${d.ten_khach}
        </div>
        ${d.so_dien_thoai ? `<div style="font-size:.77rem;color:#718096;margin-bottom:.15rem;">📞 ${d.so_dien_thoai}</div>` : ''}
        <div style="font-size:.77rem;color:#718096;margin-bottom:.4rem;">📍 ${d.dia_chi}</div>
        ${d.cod_amount > 0 ? `<div style="font-size:.75rem;font-weight:600;color:#0C9B73;margin-bottom:.4rem;">💰 COD: ${d.cod_amount.toLocaleString('vi-VN')}đ</div>` : ''}
        <div style="display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:600;padding:.18rem .55rem;border-radius:20px;background:${badgeBg};color:${badgeColor};">
            ${badgeIcon} ${d.trang_thai}
        </div>
        <div style="margin-top:.65rem;display:flex;gap:.5rem;">
            <a href="/driver/orders/${d.id}" style="font-size:.77rem;font-weight:600;color:#4F8EF7;">→ Chi tiết</a>
            <a href="${navLink}" target="_blank" style="font-size:.77rem;font-weight:600;color:#22d3a0;">🗺 Chỉ đường</a>
        </div>
    </div>`;
}

function refreshRouteSummary(list) {
    const total = list.length;
    const completed = list.filter(d => d.is_done).length;
    const remaining = total - completed;

    const totalEl = document.getElementById('countDeliveries');
    const completedEl = document.getElementById('completedDeliveries');
    const remainingEl = document.getElementById('remainingDeliveries');

    if (totalEl) totalEl.textContent = total;
    if (completedEl) completedEl.textContent = completed;
    if (remainingEl) remainingEl.textContent = remaining;
}

function focusNextStop() {
    const nextIdxInput = document.getElementById('nextStopIndex');
    if (!nextIdxInput) return;

    const nextIdx = Number(nextIdxInput.value);
    if (!Number.isNaN(nextIdx)) {
        focusDelivery(nextIdx);
    }
}

addMarkers(deliveries);

// ══════════════════════════════════════════════════════════════════
//  POLYLINE
// ══════════════════════════════════════════════════════════════════
function drawPolyline(waypoints) {
    if (polyline) map.removeLayer(polyline);
    if (waypoints.length < 2) return;
    polyline = L.polyline(waypoints, {
        color: '#4F8EF7', weight: 4, opacity: .75, dashArray: '10 6', lineJoin: 'round',
    }).addTo(map);
}

@if($optimizedResult && count($optimizedResult['waypoints']) > 1)
// Server đã tối ưu từ GPS có sẵn → vẽ polyline ngay
drawPolyline(@json($optimizedResult['waypoints']));
@endif

// ══════════════════════════════════════════════════════════════════
//  FIT BOUNDS
// ══════════════════════════════════════════════════════════════════
function fitAllMarkers() {
    const all = markerObjects.map(m => m.marker);
    if (myLocMarker) all.push(myLocMarker);
    if (all.length > 0) map.fitBounds(L.featureGroup(all).getBounds().pad(.15));
}
if (markerObjects.length > 0) {
    fitAllMarkers();
    setTimeout(() => focusNextStop(), 250);
}

// ══════════════════════════════════════════════════════════════════
//  FOCUS SIDEBAR ITEM
// ══════════════════════════════════════════════════════════════════
function focusDelivery(idx) {
    const found = markerObjects.find(m => m.idx === idx);
    if (!found) { alert('Đơn này chưa có tọa độ địa chỉ.'); return; }
    map.flyTo([found.d.lat, found.d.lng], 16, { animate: true, duration: .8 });
    found.marker.openPopup();

    // highlight sidebar
    document.querySelectorAll('.rop-item').forEach(el => el.classList.remove('active'));
    const el = document.getElementById('rop-' + idx);
    if (el) { el.classList.add('active'); el.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
}

// ══════════════════════════════════════════════════════════════════
//  SERVER-SIDE OPTIMIZE (gọi /driver/route/optimize)
// ══════════════════════════════════════════════════════════════════
async function serverOptimize(lat, lng) {
    const useLat = lat ?? driverLat;
    const useLng = lng ?? driverLng;

    if (!useLat || !useLng) {
        locateAndOptimize();   // chưa có GPS → bật geolocation
        return;
    }

    const btn = document.getElementById('optimizeBtn');
    btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Đang tính...';
    btn.disabled = true;

    try {
        const res = await fetch(OPTIMIZE_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ lat: useLat, lng: useLng }),
        });
        const data = await res.json();

        deliveries = data.sorted;
        renderSidebar(deliveries);
        addMarkers(deliveries);
        drawPolyline(data.waypoints);
        refreshRouteSummary(deliveries);

        const nextStopIndex = deliveries.findIndex(d => d.is_next);
        const nextStopIndexEl = document.getElementById('nextStopIndex');
        if (nextStopIndexEl) {
            nextStopIndexEl.value = nextStopIndex;
        }

        document.getElementById('estKm').textContent = data.total_km + ' km';
        document.getElementById('optimizedBadge').style.display = 'inline-flex';

        if (nextStopIndex >= 0) {
            focusDelivery(nextStopIndex);
        }

        // Zoom ra thấy tất cả
        const pts = data.waypoints.map(p => L.latLng(p[0], p[1]));
        if (pts.length > 0) map.fitBounds(L.latLngBounds(pts).pad(.15));

    } catch(e) {
        alert('Lỗi khi tối ưu lộ trình. Vui lòng thử lại.');
    } finally {
        btn.innerHTML = '<i class="bi bi-magic"></i> Tối ưu lộ trình';
        btn.disabled = false;
    }
}

// ══════════════════════════════════════════════════════════════════
//  GEOLOCATION → lấy vị trí → gọi serverOptimize
// ══════════════════════════════════════════════════════════════════
function locateAndOptimize() {
    const btn = document.getElementById('locateBtn');
    btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Đang tìm...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            driverLat = lat;
            driverLng = lng;

            // Cập nhật marker vị trí tôi
            if (myLocMarker) map.removeLayer(myLocMarker);
            myLocMarker = L.marker([lat, lng], { icon: myLocIcon(), zIndexOffset: 1000 })
                .bindPopup('<strong>📍 Vị trí của tôi</strong>').addTo(map);

            btn.innerHTML = '<i class="bi bi-crosshair2"></i> Vị trí &amp; Lộ trình';
            btn.disabled = false;

            serverOptimize(lat, lng);
        },
        err => {
            alert('Không thể lấy vị trí. Vui lòng cấp quyền định vị.');
            btn.innerHTML = '<i class="bi bi-crosshair2"></i> Vị trí &amp; Lộ trình';
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
    );
}

// ══════════════════════════════════════════════════════════════════
//  GPS TRACKING (cập nhật vị trí mỗi 15 giây)
// ══════════════════════════════════════════════════════════════════
let trackingInterval = null;
let isTracking = false;

function toggleTracking() {
    if (isTracking) {
        clearInterval(trackingInterval);
        isTracking = false;
        document.getElementById('trackDot').classList.remove('active');
        document.getElementById('trackBtn').innerHTML = '<i class="bi bi-broadcast"></i> Theo dõi GPS <span id="trackDot"></span>';
    } else {
        startTracking();
    }
}

function startTracking() {
    if (!navigator.geolocation) { alert('Trình duyệt không hỗ trợ GPS.'); return; }
    isTracking = true;
    document.getElementById('trackDot').classList.add('active');

    const sendPosition = () => {
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            driverLat = lat; driverLng = lng;

            // Cập nhật marker
            if (myLocMarker) myLocMarker.setLatLng([lat, lng]);
            else {
                myLocMarker = L.marker([lat, lng], { icon: myLocIcon(), zIndexOffset: 1000 })
                    .bindPopup('<strong>📍 Vị trí của tôi</strong>').addTo(map);
            }

            // Gửi lên server
            fetch(UPDATE_POS_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ lat, lng }),
            });
        }, null, { enableHighAccuracy: true, maximumAge: 10000 });
    };

    sendPosition();
    trackingInterval = setInterval(sendPosition, 15000);  // mỗi 15 giây
}

// ══════════════════════════════════════════════════════════════════
//  QUICK STATUS SHEET (cập nhật trạng thái từ popup)
// ══════════════════════════════════════════════════════════════════
let activeOrder = null;

function openSheet(d) {
    if (d.is_done) return;
    activeOrder = d;
    document.getElementById('sheetTitle').textContent = 'Đơn ' + d.ma_don + ' — ' + d.ten_khach;
    document.getElementById('sheetAddr').textContent  = '📍 ' + d.dia_chi;
    document.getElementById('statusSheet').classList.add('open');
}

function closeSheet() {
    document.getElementById('statusSheet').classList.remove('open');
    activeOrder = null;
}

async function submitStatus(action) {
    if (!activeOrder) return;

    // action: 'confirm' = HOAN_THANH, 'fail' = HUY
    const trangThaiId = action === 'confirm' ? HOAN_THANH_ID : HUY_ID;
    const ghiChu      = action === 'confirm' ? 'Giao hàng thành công' : 'Giao hàng thất bại';

    const url = `/driver/orders/${activeOrder.id}/update-status`;
    const form = new FormData();
    form.append('_token', CSRF);
    form.append('trang_thai_id', trangThaiId);
    form.append('ghi_chu', ghiChu);

    try {
        const res = await fetch(url, { method: 'POST', body: form });

        if (res.ok || res.redirected) {
            // Cập nhật UI cục bộ
            activeOrder.is_done     = (action === 'confirm');
            activeOrder.trang_thai  = action === 'confirm' ? 'Đã giao' : 'Đã huỷ';
            activeOrder.trang_thai_id = trangThaiId;

            // Cập nhật marker
            const found = markerObjects.find(m => m.d.id === activeOrder.id);
            if (found) {
                found.d = activeOrder;
                const color = action === 'confirm' ? '#22d3a0' : '#FC8181';
                found.marker.setIcon(makeIcon(color, found.idx + 1));
                found.marker.setPopupContent(buildPopup(activeOrder, found.idx));
            }

            // Cập nhật sidebar
            renderSidebar(deliveries);
            refreshRouteSummary(deliveries);
            closeSheet();

            const nextStopIndex = deliveries.findIndex(d => d.is_next);
            if (nextStopIndex >= 0) {
                focusDelivery(nextStopIndex);
            }
        } else {
            alert('Lỗi cập nhật trạng thái. Thử lại sau.');
        }
    } catch(e) {
        alert('Lỗi kết nối. Vui lòng kiểm tra mạng.');
    }
}

// ══════════════════════════════════════════════════════════════════
//  RENDER SIDEBAR (re-render từ JS array)
// ══════════════════════════════════════════════════════════════════
function renderSidebar(list) {
    const el = document.getElementById('orderListItems');
    if (!el) return;

    el.innerHTML = list.map((d, i) => {
        const navUrl = d.lat && d.lng
            ? `https://www.google.com/maps/dir/?api=1&destination=${d.lat},${d.lng}`
            : (d.dia_chi !== '—' ? `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(d.dia_chi)}` : null);

        const codHtml = d.cod_amount > 0
            ? `<div class="rop-cod"><i class="bi bi-cash me-1"></i>COD: ${d.cod_amount.toLocaleString('vi-VN')}đ</div>`
            : '';
        const nextLabel = d.is_next
            ? `<div class="rop-next-label"><i class="bi bi-lightning-charge-fill me-1"></i>Điểm tiếp theo</div>`
            : '';
        const statusClass = d.is_done ? 'done' : (d.is_next ? 'next-stop' : 'pending');
        const statusIcon = d.is_done ? 'check2-circle' : (d.is_next ? 'signpost-split' : 'clock');
        const coordBadge = !d.has_coords
            ? `<span class="rop-badge muted"><i class="bi bi-exclamation-triangle"></i>Chưa có tọa độ</span>`
            : '';

        return `
        <div class="rop-item ${d.is_done ? 'done' : ''} ${d.is_next ? 'next-stop' : ''}" id="rop-${i}" onclick="focusDelivery(${i})">
            <div class="rop-num ${d.is_done ? 'done' : ''} ${d.is_next ? 'next-stop' : ''}">${i + 1}</div>
            <div class="rop-info">
                ${nextLabel}
                <div class="rop-name">${d.ten_khach}</div>
                <div class="rop-addr"><i class="bi bi-geo-alt"></i> ${d.dia_chi.substring(0, 36)}${d.dia_chi.length > 36 ? '...' : ''}</div>
                <div class="rop-meta">
                    <span class="rop-status ${statusClass}">
                        <i class="bi bi-${statusIcon}"></i> ${d.trang_thai}
                    </span>
                    ${coordBadge}
                </div>
                ${codHtml}
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                ${navUrl ? `<a href="${navUrl}" target="_blank" class="rop-navbtn" onclick="event.stopPropagation()"><i class="bi bi-send-fill"></i></a>` : ''}
                <a href="/driver/orders/${d.id}" class="rop-eyebtn" onclick="event.stopPropagation()"><i class="bi bi-eye"></i></a>
            </div>
        </div>`;
    }).join('');
}

// ══════════════════════════════════════════════════════════════════
//  SPIN & PULSE ANIMATIONS
// ══════════════════════════════════════════════════════════════════
const styleEl = document.createElement('style');
styleEl.textContent = `
    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { display:inline-block; animation: spin .6s linear infinite; }
    @keyframes pulse-dot {
        0%,100% { box-shadow: 0 0 0 0 rgba(255,107,43,.6); }
        50%      { box-shadow: 0 0 0 10px rgba(255,107,43,0); }
    }
`;
document.head.appendChild(styleEl);

// ══════════════════════════════════════════════════════════════════
//  AUTO-BOOT: Nếu đã có GPS từ DB thì bật tracking ngay
// ══════════════════════════════════════════════════════════════════
@if($taiXeLat && $taiXeLng)
// Đã có GPS → bắt đầu tracking tự động sau 2s
setTimeout(() => startTracking(), 2000);
@else
// Chưa có GPS → tự động locate sau 1s
setTimeout(() => locateAndOptimize(), 1000);
@endif
</script>
@endpush
