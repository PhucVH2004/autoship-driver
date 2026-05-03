{{--
    admin/map/index.blade.php
    Bản đồ giao hàng — Leaflet.js + data thật từ MapController
    Markers: tài xế (GPS mới nhất), khách hàng (lat/lng), đơn đang giao
--}}
@extends('layouts.admin')

@section('page_title', 'Bản đồ giao hàng')

@push('extra_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #delivery-map {
        height: calc(100vh - 260px);
        min-height: 500px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,.08);
    }
    .driver-panel {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,.08);
        height: calc(100vh - 260px);
        min-height: 500px;
        overflow-y: auto;
    }
    .driver-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: background .15s;
    }
    .driver-item:hover { background: #f8fafc; }
    .driver-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    /* Legend */
    .map-legend { display: flex; gap: 16px; flex-wrap: wrap; }
    .legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading">Bản đồ giao hàng</h1>
        <p class="page-subtext">Theo dõi vị trí tài xế và đơn hàng đang giao</p>
    </div>
    {{-- Chú thích loại marker --}}
    <div class="map-legend">
        <span class="d-flex align-items-center gap-2" style="font-size:.82rem; color:#475569;">
            <span class="legend-dot" style="background:#3b82f6;"></span>Tài xế đang giao
        </span>
        <span class="d-flex align-items-center gap-2" style="font-size:.82rem; color:#475569;">
            <span class="legend-dot" style="background:#94a3b8;"></span>Tài xế rảnh
        </span>
        <span class="d-flex align-items-center gap-2" style="font-size:.82rem; color:#475569;">
            <span class="legend-dot" style="background:#f59e0b;"></span>Khách hàng
        </span>
        <span class="d-flex align-items-center gap-2" style="font-size:.82rem; color:#475569;">
            <span class="legend-dot" style="background:#ef4444;"></span>Đơn đang giao
        </span>
    </div>
</div>

<div class="row g-3">

    {{-- ── BẢN ĐỒ ────────────────────────────────────────────────────────── --}}
    <div class="col-lg-9">
        <div id="delivery-map"></div>
    </div>

    {{-- ── PANEL TÀI XẾ (data thật từ $taiXes) ───────────────────────────── --}}
    <div class="col-lg-3">
        <div class="driver-panel">
            <div class="px-4 py-3 border-bottom">
                <h6 class="fw-bold mb-0" style="font-size:.9rem;">
                    <i class="bi bi-person-badge me-2 text-primary"></i>
                    Tài xế hoạt động
                    <span class="badge bg-primary ms-1">{{ count($taiXes) }}</span>
                </h6>
            </div>

            @if(count($taiXes) > 0)
            @foreach($taiXes as $tx)
            @php
                $dotColor = $tx['trang_thai'] === 'Đang giao' ? '#3b82f6' : '#94a3b8';
            @endphp
            <div class="driver-item" onclick="focusDriver({{ $tx['id'] }})">
                <span class="driver-dot" style="background:{{ $dotColor }};"></span>
                <div class="flex-grow-1 min-w-0">
                    <div style="font-weight:600; font-size:.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $tx['ho_ten'] }}
                    </div>
                    <div style="font-size:.76rem; color:#64748b;">
                        {{ $tx['trang_thai'] }}
                        @if($tx['bien_so_xe'])
                            · {{ $tx['bien_so_xe'] }}
                        @endif
                    </div>
                    @if($tx['latitude'] && $tx['longitude'])
                        <div style="font-size:.72rem; color:#94a3b8;">
                            <i class="bi bi-geo-alt me-1"></i>{{ number_format($tx['latitude'],4) }}, {{ number_format($tx['longitude'],4) }}
                            @if($tx['cap_nhat'])
                                · {{ $tx['cap_nhat'] }}
                            @endif
                        </div>
                    @else
                        <div style="font-size:.72rem; color:#e2e8f0;">Chưa có vị trí GPS</div>
                    @endif
                </div>
                <button class="btn btn-sm btn-outline-secondary rounded-2" style="font-size:.72rem; padding:3px 7px;"
                        onclick="focusDriver({{ $tx['id'] }}); event.stopPropagation();" title="Phóng to vị trí">
                    <i class="bi bi-geo-alt"></i>
                </button>
            </div>
            @endforeach
            @else
            <div class="text-center py-5 text-muted" style="font-size:.88rem;">
                <i class="bi bi-person-slash d-block fs-4 mb-2"></i>
                Không có tài xế hoạt động
            </div>
            @endif

            {{-- Thống kê đơn đang giao --}}
            <div class="px-4 py-3 border-top mt-auto" style="background:#f8fafc;">
                <div style="font-size:.78rem; color:#64748b;">
                    <i class="bi bi-box-seam me-1"></i>
                    Đơn đang giao: <strong>{{ count($donHangDangGiao) }}</strong>
                </div>
                <div style="font-size:.78rem; color:#64748b; margin-top:4px;">
                    <i class="bi bi-people me-1"></i>
                    Khách hàng có vị trí: <strong>{{ count($khachHangs) }}</strong>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('extra_js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ───────────────────────────────────────────────────
// Khởi tạo bản đồ — tâm TP. Hồ Chí Minh, zoom 12
// ───────────────────────────────────────────────────
const map = L.map('delivery-map').setView([10.7769, 106.7009], 12);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
    maxZoom: 18,
}).addTo(map);

// Lưu markers để focusDriver() có thể pan đến
const driverMarkers = {};

// ───────────────────────────────────────────────────
// PHẦN 1: Marker TÀI XẾ (data từ $taiXes Controller)
// ───────────────────────────────────────────────────
const taiXesData = @json($taiXes);

taiXesData.forEach(function(tx) {
    if (!tx.latitude || !tx.longitude) return; // Bỏ qua nếu chưa có GPS

    const isDangGiao = tx.trang_thai === 'Đang giao';
    const fillColor  = isDangGiao ? '#3b82f6' : '#94a3b8';

    const marker = L.circleMarker([tx.latitude, tx.longitude], {
        radius      : 12,
        fillColor   : fillColor,
        color       : '#ffffff',
        weight      : 3,
        opacity     : 1,
        fillOpacity : 0.9,
    }).addTo(map);

    marker.bindPopup(`
        <div style="min-width:160px;">
            <div style="font-weight:700; font-size:.9rem; margin-bottom:4px;">
                🚛 ${tx.ho_ten}
            </div>
            <div style="font-size:.82rem; color:#64748b;">Trạng thái: <strong>${tx.trang_thai}</strong></div>
            ${tx.bien_so_xe ? `<div style="font-size:.8rem; margin-top:3px;">🪪 ${tx.bien_so_xe}</div>` : ''}
            ${tx.cap_nhat   ? `<div style="font-size:.75rem; color:#94a3b8; margin-top:4px;">Cập nhật: ${tx.cap_nhat}</div>` : ''}
        </div>
    `);

    driverMarkers[tx.id] = marker;
});

// ───────────────────────────────────────────────────
// PHẦN 2: Marker KHÁCH HÀNG (data từ $khachHangs)
// ───────────────────────────────────────────────────
const khachHangsData = @json($khachHangs);

// Icon nhà (vòng tròn màu vàng)
const customerIcon = L.divIcon({
    html: '<div style="background:#f59e0b;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3);"></div>',
    iconSize : [14, 14],
    iconAnchor: [7, 7],
    className: '',
});

khachHangsData.forEach(function(kh) {
    if (!kh.latitude || !kh.longitude) return;

    L.marker([kh.latitude, kh.longitude], { icon: customerIcon })
        .addTo(map)
        .bindPopup(`
            <div style="min-width:160px;">
                <div style="font-weight:700; font-size:.9rem; margin-bottom:4px;">
                    🏠 ${kh.ten_khach}
                </div>
                <div style="font-size:.8rem; color:#64748b; margin-top:3px;">📞 ${kh.so_dien_thoai || '—'}</div>
                <div style="font-size:.8rem; margin-top:3px;">📍 ${kh.dia_chi || '—'}</div>
            </div>
        `);
});

// ───────────────────────────────────────────────────
// PHẦN 3: Marker ĐƠN HÀNG ĐANG GIAO (data từ $donHangDangGiao)
// ───────────────────────────────────────────────────
const donHangsData = @json($donHangDangGiao);

// Icon hộp hàng (vòng tròn đỏ, nhỏ hơn)
const orderIcon = L.divIcon({
    html: '<div style="background:#ef4444;width:12px;height:12px;border-radius:3px;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3);transform:rotate(45deg);"></div>',
    iconSize : [12, 12],
    iconAnchor: [6, 6],
    className: '',
});

donHangsData.forEach(function(dh) {
    // Dùng tọa độ khách hàng của đơn hàng đó (đây là điểm giao)
    if (!dh.khach_hang || !dh.khach_hang.latitude || !dh.khach_hang.longitude) return;

    L.marker([dh.khach_hang.latitude, dh.khach_hang.longitude], { icon: orderIcon })
        .addTo(map)
        .bindPopup(`
            <div style="min-width:160px;">
                <div style="font-weight:700; font-size:.9rem; margin-bottom:4px;">
                    📦 ${dh.ma_don}
                </div>
                <div style="font-size:.82rem; color:#64748b;">
                    Trạng thái: <strong>${dh.trang_thai ? dh.trang_thai.ten_trang_thai : '—'}</strong>
                </div>
                <div style="font-size:.8rem; margin-top:3px;">
                    👤 KH: ${dh.khach_hang ? dh.khach_hang.ten_khach : '—'}
                </div>
                <div style="font-size:.8rem; margin-top:3px;">
                    🚛 TX: ${dh.tai_xe ? dh.tai_xe.ho_ten : 'Chưa phân công'}
                </div>
            </div>
        `);
});

// ───────────────────────────────────────────────────
// Pan đến tài xế khi click panel bên phải
// ───────────────────────────────────────────────────
function focusDriver(id) {
    const marker = driverMarkers[id];
    if (marker) {
        map.setView(marker.getLatLng(), 15, { animate: true });
        marker.openPopup();
    } else {
        alert('Tài xế này chưa cập nhật vị trí GPS.');
    }
}

// ───────────────────────────────────────────────────
// PHẦN 4: AUTO CẬP NHẬT VỊ TRÍ TÀI XẾ (Realtime REAL-TIME Tracking)
// ───────────────────────────────────────────────────
function fetchDriverLocations() {
    fetch("{{ route('admin.api.drivers.location') }}")
        .then(res => res.json())
        .then(drivers => {
            drivers.forEach(tx => {
                if (!tx.lat || !tx.lng) return;

                const isDangGiao = tx.status === 'Đang giao';
                const fillColor  = isDangGiao ? '#3b82f6' : '#94a3b8';

                // Nếu marker đã có, thì cập nhật tọa độ
                if (driverMarkers[tx.id]) {
                    driverMarkers[tx.id].setLatLng([tx.lat, tx.lng]);
                    driverMarkers[tx.id].setStyle({ fillColor: fillColor });
                    
                    // Cập nhật nội dung Popup (status / last_update)
                    driverMarkers[tx.id].setPopupContent(`
                        <div style="min-width:160px;">
                            <div style="font-weight:700; font-size:.9rem; margin-bottom:4px;">
                                🚛 ${tx.name}
                            </div>
                            <div style="font-size:.82rem; color:#64748b;">Trạng thái: <strong>${tx.status}</strong></div>
                            ${tx.plate ? `<div style="font-size:.8rem; margin-top:3px;">🪪 ${tx.plate}</div>` : ''}
                            ${tx.last_update ? `<div style="font-size:.75rem; color:#94a3b8; margin-top:4px;">Cập nhật mới nhất: ${tx.last_update}</div>` : ''}
                        </div>
                    `);
                } else {
                    // Nếu chưa có (tài xế vừa online) thì tạo marker mới
                    const marker = L.circleMarker([tx.lat, tx.lng], {
                        radius      : 12,
                        fillColor   : fillColor,
                        color       : '#ffffff',
                        weight      : 3,
                        opacity     : 1,
                        fillOpacity : 0.9,
                    }).addTo(map);

                    marker.bindPopup(`
                        <div style="min-width:160px;">
                            <div style="font-weight:700; font-size:.9rem; margin-bottom:4px;">
                                🚛 ${tx.name}
                            </div>
                            <div style="font-size:.82rem; color:#64748b;">Trạng thái: <strong>${tx.status}</strong></div>
                            ${tx.plate ? `<div style="font-size:.8rem; margin-top:3px;">🪪 ${tx.plate}</div>` : ''}
                            ${tx.last_update ? `<div style="font-size:.75rem; color:#94a3b8; margin-top:4px;">Cập nhật mới nhất: ${tx.last_update}</div>` : ''}
                        </div>
                    `);

                    driverMarkers[tx.id] = marker;
                }
            });
        })
        .catch(err => console.error('Lỗi khi fetch vị trí tài xế:', err));
}

// Chạy auto fetch mỗi 5 giây
setInterval(fetchDriverLocations, 5000);

</script>
@endpush
