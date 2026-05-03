@extends('layouts.driver')

@section('page_title', 'Lộ trình hôm nay')

@section('content')

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
            Bản đồ các điểm giao hàng được phân công — {{ now()->format('d/m/Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="route-action-btn" onclick="locateMe()" id="locateBtn">
            <i class="bi bi-crosshair2"></i>
            Vị trí của tôi
        </button>
        <button class="route-action-btn" onclick="fitAllMarkers()">
            <i class="bi bi-fullscreen"></i>
            Xem toàn lộ trình
        </button>
    </div>
</div>

{{-- ── STAT ROW ─────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xxl-3">
        <div class="route-mini-card" style="--c:#4F8EF7;">
            <div class="rmc-icon"><i class="bi bi-geo-alt-fill"></i></div>
            <div class="rmc-val" id="countDeliveries">{{ count($donHangs ?? []) }}</div>
            <div class="rmc-label">Điểm giao</div>
        </div>
    </div>
    <div class="col-6 col-xxl-3">
        <div class="route-mini-card" style="--c:#22d3a0;">
            <div class="rmc-icon"><i class="bi bi-check2-circle"></i></div>
            <div class="rmc-val" id="countDone">0</div>
            <div class="rmc-label">Đã giao xong</div>
        </div>
    </div>
    <div class="col-6 col-xxl-3">
        <div class="route-mini-card" style="--c:#FF6B2B;">
            <div class="rmc-icon"><i class="bi bi-truck"></i></div>
            <div class="rmc-val" id="countPend">0</div>
            <div class="rmc-label">Còn lại</div>
        </div>
    </div>
    <div class="col-6 col-xxl-3">
        <div class="route-mini-card" style="--c:#A78BFA;">
            <div class="rmc-icon"><i class="bi bi-signpost-2"></i></div>
            <div class="rmc-val" id="estKm">—</div>
            <div class="rmc-label">Ước tính km</div>
        </div>
    </div>
</div>

{{-- ── MAP + SIDEBAR LAYOUT ─────────────────────── --}}
<div class="route-wrapper">

    {{-- Orders sidebar --}}
    <div class="route-orders-panel">
        <div class="rop-header">
            <i class="bi bi-list-check me-1"></i>
            Danh sách điểm giao
        </div>
        <div class="rop-body" id="routeOrderList">
            @forelse($donHangs ?? [] as $idx => $dh)
            @php
                $kh = $dh->khachHang;
                $lat = $kh?->latitude;
                $lng = $kh?->longitude;
                $tenTT = $dh->trangThai?->ten_trang_thai ?? '';
                $isDone = str_contains(strtolower($tenTT), 'thành');
            @endphp
            <div class="rop-item {{ $isDone ? 'done' : '' }}"
                 data-lat="{{ $lat }}"
                 data-lng="{{ $lng }}"
                 onclick="focusMarker({{ $idx }}, {{ $lat ?? 'null' }}, {{ $lng ?? 'null' }})">
                <div class="rop-num {{ $isDone ? 'done' : '' }}">{{ $idx + 1 }}</div>
                <div class="rop-info">
                    <div class="rop-name">{{ $kh?->ten_khach ?? 'Khách hàng' }}</div>
                    <div class="rop-addr">
                        <i class="bi bi-geo-alt"></i>
                        {{ Str::limit($kh?->dia_chi ?? '—', 40) }}
                    </div>
                    <div class="rop-status {{ $isDone ? 'done' : 'pending' }}">
                        <i class="bi bi-{{ $isDone ? 'check2-circle' : 'clock' }}"></i>
                        {{ $tenTT ?: 'Chờ giao' }}
                    </div>
                </div>
                <a href="{{ route('driver.orders.show', $dh->id) }}" class="rop-eyebtn" title="Chi tiết" onclick="event.stopPropagation()">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
            @empty
            <div class="text-center p-4" style="color:#A0AEC0;">
                <i class="bi bi-map-fill d-block fs-2 mb-2"></i>
                <p style="font-size:.85rem;">Chưa có đơn nào hôm nay</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Map --}}
    <div class="route-map-wrap">
        <div id="deliveryMap"></div>
        <div class="map-legend">
            <div class="legend-item"><span class="legend-dot" style="background:#4F8EF7;"></span> Điểm giao</div>
            <div class="legend-item"><span class="legend-dot" style="background:#22d3a0;"></span> Đã hoàn thành</div>
            <div class="legend-item"><span class="legend-dot" style="background:#FF6B2B;"></span> Đang giao</div>
            <div class="legend-item"><span class="legend-dot" style="background:#1A1D2E;box-shadow:0 0 0 2px #FF6B2B;"></span> Vị trí tôi</div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
/* Route action btns */
.route-action-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .5rem 1rem;
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    color: #4A5568;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}
.route-action-btn:hover {
    background: linear-gradient(135deg, #FF6B2B, #FF9A5C);
    color: #fff;
    border-color: #FF6B2B;
    box-shadow: 0 4px 12px rgba(255,107,43,.3);
}

/* Mini cards */
.route-mini-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #E2E8F0;
    padding: 1rem;
    display: flex; align-items: center; gap: .85rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.rmc-icon {
    width: 42px; height: 42px;
    border-radius: 11px;
    background: color-mix(in srgb, var(--c) 12%, transparent);
    color: var(--c);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.rmc-val   { font-size: 1.5rem; font-weight: 800; color: #1A202C; }
.rmc-label { font-size: .72rem; color: #A0AEC0; font-weight: 500; }

/* Main wrapper */
.route-wrapper {
    display: flex;
    gap: 1.25rem;
    height: 600px;
}

/* Orders sidebar panel */
.route-orders-panel {
    width: 280px;
    flex-shrink: 0;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #E2E8F0;
    display: flex; flex-direction: column;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.04);
}
.rop-header {
    padding: .9rem 1rem;
    border-bottom: 1px solid #F0F4F8;
    font-size: .85rem; font-weight: 700; color: #2D3748;
}
.rop-body {
    flex: 1;
    overflow-y: auto;
    padding: .5rem;
}
.rop-body::-webkit-scrollbar { width: 3px; }
.rop-body::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 2px; }

.rop-item {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .75rem .65rem;
    border-radius: 10px;
    cursor: pointer;
    transition: all .15s;
    margin-bottom: .35rem;
    border: 1px solid transparent;
}
.rop-item:hover { background: #F7FAFC; border-color: #E2E8F0; }
.rop-item.done  { opacity: .6; }

.rop-num {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: #4F8EF7;
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    margin-top: .1rem;
}
.rop-num.done { background: #22d3a0; }

.rop-info { flex: 1; min-width: 0; }
.rop-name { font-size: .85rem; font-weight: 600; color: #2D3748; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rop-addr { font-size: .74rem; color: #A0AEC0; margin-top: .1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rop-status {
    font-size: .7rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: .3rem;
    margin-top: .3rem;
    padding: .15rem .5rem;
    border-radius: 20px;
}
.rop-status.done    { background: rgba(34,211,160,.1); color: #0C9B73; }
.rop-status.pending { background: rgba(255,107,43,.1);  color: #C2490D; }

.rop-eyebtn {
    width: 28px; height: 28px;
    border-radius: 7px;
    background: rgba(79,142,247,.1);
    color: #4F8EF7;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    font-size: .85rem;
    flex-shrink: 0;
    transition: all .2s;
    margin-top: .1rem;
}
.rop-eyebtn:hover { background: #4F8EF7; color: #fff; }

/* Map container */
.route-map-wrap {
    flex: 1;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    border: 1px solid #E2E8F0;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
}

#deliveryMap {
    width: 100%;
    height: 100%;
    min-height: 600px;
}

.map-legend {
    position: absolute;
    bottom: 1.25rem; right: 1.25rem;
    background: rgba(26,29,46,.88);
    backdrop-filter: blur(6px);
    border-radius: 10px;
    padding: .65rem .9rem;
    z-index: 500;
    display: flex; flex-direction: column; gap: .4rem;
}
.legend-item {
    display: flex; align-items: center; gap: .5rem;
    font-size: .75rem; color: rgba(255,255,255,.8);
}
.legend-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .route-wrapper {
        flex-direction: column;
        height: auto;
    }
    .route-orders-panel {
        width: 100%;
        height: 240px;
    }
    #deliveryMap { min-height: 400px; }
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
// ── Delivery data passed from Blade ──
@php
$deliveryData = collect($donHangs ?? [])->map(function($dh) {
    return [
        'id'       => $dh->id,
        'ma_don'   => $dh->ma_don,
        'ten_khach'=> $dh->khachHang?->ten_khach ?? '—',
        'so_dien_thoai' => $dh->khachHang?->so_dien_thoai ?? '—',
        'dia_chi'  => $dh->khachHang?->dia_chi ?? '—',
        'lat'      => (float) ($dh->khachHang?->latitude  ?? 0),
        'lng'      => (float) ($dh->khachHang?->longitude ?? 0),
        'trang_thai'    => $dh->trangThai?->ten_trang_thai ?? 'Chờ giao',
        'is_done'  => str_contains(strtolower($dh->trangThai?->ten_trang_thai ?? ''), 'thành'),
    ];
});
@endphp
const deliveries = @json($deliveryData);

// ── Init Leaflet Map ──
const defaultCenter = [10.7769, 106.7009]; // HCM City

const map = L.map('deliveryMap', {
    center: defaultCenter,
    zoom: 12,
    zoomControl: true,
});

// Tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
    maxZoom: 19,
}).addTo(map);

// ── Custom marker icons ──
function makeIcon(color, size = 36) {
    return L.divIcon({
        className: '',
        html: `<div style="
            width:${size}px;height:${size}px;
            border-radius:50% 50% 50% 0;
            background:${color};
            border:3px solid #fff;
            box-shadow:0 3px 10px rgba(0,0,0,.3);
            transform:rotate(-45deg);
            display:flex;align-items:center;justify-content:center;
        "></div>`,
        iconSize: [size, size],
        iconAnchor: [size/2, size],
        popupAnchor: [0, -size],
    });
}

const markers = [];
let done = 0;
let pending = 0;

deliveries.forEach((d, idx) => {
    if (!d.lat || !d.lng) return;

    const color = d.is_done ? '#22d3a0' : '#FF6B2B';
    const marker = L.marker([d.lat, d.lng], { icon: makeIcon(color) })
        .bindPopup(`
            <div style="min-width:200px;font-family:'Inter',sans-serif;">
                <div style="font-weight:700;font-size:.95rem;margin-bottom:.4rem;color:#1A1D2E;">
                    <span style="background:#4F8EF7;color:#fff;border-radius:50%;width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;margin-right:.4rem;">${idx+1}</span>
                    ${d.ten_khach}
                </div>
                <div style="font-size:.8rem;color:#718096;margin-bottom:.25rem;">📞 ${d.so_dien_thoai}</div>
                <div style="font-size:.8rem;color:#718096;margin-bottom:.5rem;">📍 ${d.dia_chi}</div>
                <div style="display:inline-flex;align-items:center;gap:.3rem;font-size:.75rem;font-weight:600;padding:.2rem .65rem;border-radius:20px;background:${d.is_done ? 'rgba(34,211,160,.12)' : 'rgba(255,107,43,.12)'};color:${d.is_done ? '#0C9B73' : '#C2490D'};">
                    ${d.is_done ? '✓' : '⏳'} ${d.trang_thai}
                </div>
                <div style="margin-top:.75rem;">
                    <a href="/driver/orders/${d.id}" style="font-size:.8rem;font-weight:600;color:#4F8EF7;">→ Xem chi tiết</a>
                </div>
            </div>
        `, { maxWidth: 260 })
        .addTo(map);

    markers.push(marker);

    if (d.is_done) done++; else pending++;
});

// Update mini cards
document.getElementById('countDone').textContent = done;
document.getElementById('countPend').textContent  = pending;

// ── Fit bounds ──
function fitAllMarkers() {
    if (markers.length > 0) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.15));
    }
}

if (markers.length > 0) fitAllMarkers();

// ── Focus single marker on sidebar click ──
function focusMarker(idx, lat, lng) {
    if (!lat || !lng) { alert('Đơn này chưa có toạ độ địa chỉ.'); return; }
    map.flyTo([lat, lng], 16, { animate: true, duration: .8 });
    if (markers[idx]) markers[idx].openPopup();
}

// ── Locate me & Calculate Route ──
let myLocMarker = null;
let routeControl = null;

function locateMe() {
    const btn = document.getElementById('locateBtn');
    btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Đang tìm...';
    
    // Calculate total kilometers element
    const estKmEl = document.getElementById('estKm');
    
    navigator.geolocation.getCurrentPosition(pos => {
        const { latitude: lat, longitude: lng } = pos.coords;
        const myIcon = L.divIcon({
            className: '',
            html: `<div style="width:18px;height:18px;border-radius:50%;background:#1A1D2E;border:3px solid #FF6B2B;box-shadow:0 0 0 6px rgba(255,107,43,.2);animation:pulse-dot 2s infinite;"></div>`,
            iconSize: [18, 18], iconAnchor: [9, 9],
        });
        
        if (myLocMarker) map.removeLayer(myLocMarker);
        myLocMarker = L.marker([lat, lng], { icon: myIcon, zIndexOffset: 1000 })
            .bindPopup('<strong>📍 Vị trí của tôi</strong>').addTo(map);
            
        btn.innerHTML = '<i class="bi bi-crosshair2"></i> Vị trí của tôi';

        // Xóa control cũ nếu có
        if (routeControl) {
            map.removeControl(routeControl);
        }

        // Tạo mảng waypoints gồm [vị trí tài xế, điểm đến 1, điểm đến 2...]
        // Chỉ lấy các đơn chưa hoàn thành để vẽ lộ trình
        let waypoints = [ L.latLng(lat, lng) ];
        
        deliveries.forEach(d => {
            if (d.lat && d.lng && !d.is_done) {
                waypoints.push(L.latLng(d.lat, d.lng));
            }
        });

        if (waypoints.length > 1) {
            routeControl = L.Routing.control({
                waypoints: waypoints,
                routeWhileDragging: false,
                addWaypoints: false,
                fitSelectedRoutes: true,
                showAlternatives: false,
                lineOptions: {
                    styles: [{color: '#4F8EF7', opacity: 0.8, weight: 6}]
                },
                createMarker: function() { return null; }, // Ẩn marker mặc định của routing module vì ta đã vẽ marker riêng ở trên
            }).addTo(map);
            
            // Xử lý sự kiện khi route được tìm thấy để tính tổng KM
            routeControl.on('routesfound', function(e) {
                var routes = e.routes;
                var summary = routes[0].summary;
                // summary.totalDistance là mét -> chuyển sang km
                var totalKm = (summary.totalDistance / 1000).toFixed(1);
                estKmEl.textContent = totalKm + " km";
            });
            
        } else {
            // Không có điểm đến nào, chỉ zoom vào tài xế
            map.flyTo([lat, lng], 15, { animate: true, duration: 1 });
            estKmEl.textContent = "0 km";
        }
        
    }, () => {
        alert('Không thể lấy vị trí. Vui lòng cấp quyền định vị.');
        btn.innerHTML = '<i class="bi bi-crosshair2"></i> Vị trí của tôi';
    });
}

// Gọi mặc định để lấy vị trí và tính lộ trình ngay khi load trang (nếu có quyền)
setTimeout(locateMe, 1000);

// spin animation
const style = document.createElement('style');
style.textContent = '@keyframes spin{to{transform:rotate(360deg)}} .spin{display:inline-block;animation:spin .6s linear infinite;}';
document.head.appendChild(style);
</script>
@endpush
