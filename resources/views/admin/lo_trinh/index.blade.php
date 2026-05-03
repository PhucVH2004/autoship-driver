@extends('layouts.admin')

@section('title', 'Lộ trình tài xế')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">Lộ trình tài xế</h2>
            <p class="text-muted mb-0">Xem lộ trình và thống kê giao hàng của từng tài xế</p>
        </div>
        <div class="col-md-4">
            <form method="GET" action="{{ route('admin.lo_trinh.index') }}" class="d-flex gap-2">
                <input type="date" name="date" class="form-control" value="{{ $routeDate }}" required>
                <button type="submit" class="btn btn-primary">Xem</button>
            </form>
        </div>
    </div>

    {{-- Driver Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên tài xế</th>
                            <th>Biển số xe</th>
                            <th class="text-center">Tổng đơn</th>
                            <th class="text-center">Đã giao</th>
                            <th class="text-center">Chưa giao</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taiXes as $index => $driver)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $driver['ho_ten'] }}</td>
                            <td>{{ $driver['bien_so_xe'] ?? '-' }}</td>
                            <td class="text-center">{{ $driver['total_orders'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $driver['completed_orders'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $driver['pending_orders'] }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $driver['trang_thai_class'] }}">
                                    {{ $driver['trang_thai_label'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button
                                    class="btn btn-sm btn-primary view-route-btn"
                                    data-driver-id="{{ $driver['id'] }}"
                                    data-driver-name="{{ $driver['ho_ten'] }}"
                                    data-date="{{ $routeDate }}">
                                    Xem lộ trình
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có tài xế nào
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Route Modal --}}
<div class="modal fade" id="routeModal" tabindex="-1" aria-labelledby="routeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-md-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="routeModalLabel">Lộ trình - <span id="driverName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0" style="height: 600px;">
                    {{-- Map Container (70%) --}}
                    <div class="col-md-8 position-relative">
                        <div id="routeMap" style="height: 100%; width: 100%;"></div>
                    </div>

                    {{-- Sidebar (30%) --}}
                    <div class="col-md-4 border-start p-3 overflow-auto">
                        {{-- Driver Info Card --}}
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-2 text-muted">Thông tin tài xế</h6>
                                <p class="mb-1"><strong>Tên:</strong> <span id="sidebarDriverName"></span></p>
                                <p class="mb-1"><strong>Biển số:</strong> <span id="sidebarBienSo"></span></p>
                                <p class="mb-0"><strong>Trạng thái:</strong> <span id="sidebarTrangThai"></span></p>
                            </div>
                        </div>

                        {{-- Statistics Card --}}
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-subtitle mb-3 text-muted">Thống kê</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tổng đơn:</span>
                                    <strong id="statTotal">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Đã giao:</span>
                                    <strong class="text-success" id="statCompleted">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Chưa giao:</span>
                                    <strong class="text-warning" id="statPending">0</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Vị trí cập nhật:</span>
                                    <small class="text-muted" id="statLastUpdate">-</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

@push('extra_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""/>
<style>
.driver-marker {
    background: transparent;
    border: none;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.badge.status-done {
    background-color: #28a745;
}

.badge.status-delivering {
    background-color: #007bff;
}

.badge.status-cancelled {
    background-color: #6c757d;
}

#routeMap {
    background-color: #f0f0f0;
}

.leaflet-popup-content {
    margin: 8px 12px;
    line-height: 1.6;
}

.leaflet-popup-content strong {
    color: #333;
    font-size: 14px;
}
</style>
@endpush

@push('extra_js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""></script>
<script>
let map = null;
let markers = [];

// Initialize map when modal is shown
document.getElementById('routeModal').addEventListener('shown.bs.modal', function() {
    if (!map) {
        map = L.map('routeMap').setView([10.762622, 106.660172], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    }

    setTimeout(() => map.invalidateSize(), 200);
});

// Clear map when modal is hidden
document.getElementById('routeModal').addEventListener('hidden.bs.modal', function() {
    if (map) {
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
    }
});

// Handle "Xem lộ trình" button click
const buttons = document.querySelectorAll('.view-route-btn');

buttons.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const driverId = this.dataset.driverId;
        const driverName = this.dataset.driverName;
        const date = this.dataset.date;

        // Update modal title
        document.getElementById('driverName').textContent = driverName;
        document.getElementById('sidebarDriverName').textContent = driverName;

        // Show modal
        const modalEl = document.getElementById('routeModal');
        const modal = new bootstrap.Modal(modalEl);

        // Wait for modal to be fully shown before fetching data
        modalEl.addEventListener('shown.bs.modal', function fetchData() {
            // Fetch route data
            const url = `/admin/lo-trinh/tai-xe/${driverId}?date=${date}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    displayRouteData(data);
                })
                .catch(error => {
                    console.error('Error fetching route data:', error);
                    alert('Không thể tải dữ liệu lộ trình: ' + error.message);
                });

            // Remove this listener after first use
            modalEl.removeEventListener('shown.bs.modal', fetchData);
        }, { once: true });

        modal.show();
    });
});

function displayRouteData(data) {
    // Update sidebar info
    document.getElementById('sidebarBienSo').textContent = data.driver.bien_so_xe || '-';
    document.getElementById('sidebarTrangThai').textContent = data.driver.trang_thai;
    document.getElementById('statTotal').textContent = data.statistics.total;
    document.getElementById('statCompleted').textContent = data.statistics.completed;
    document.getElementById('statPending').textContent = data.statistics.pending;
    document.getElementById('statLastUpdate').textContent = data.driver.last_update || '-';

    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    // Add order markers
    const bounds = [];

    data.orders.forEach(order => {
        const lat = order.khach_hang.latitude;
        const lng = order.khach_hang.longitude;

        if (!lat || !lng) return;

        bounds.push([lat, lng]);

        // Determine marker color
        let markerColor = 'orange'; // pending
        if (order.trang_thai.id === 5) { // DA_GIAO
            markerColor = 'green';
        } else if (order.is_current) {
            markerColor = 'blue';
        }

        const marker = L.circleMarker([lat, lng], {
            radius: 8,
            fillColor: markerColor,
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map);

        marker.bindPopup(`
            <strong>${order.ma_don}</strong><br>
            ${order.khach_hang.ten_khach}<br>
            ${order.khach_hang.dia_chi}<br>
            <span class="badge bg-secondary">${order.trang_thai.ten_trang_thai}</span><br>
            ${order.cod_amount ? 'COD: ' + order.cod_amount.toLocaleString() + ' đ' : ''}
        `);

        markers.push(marker);
    });

    // Add driver location marker if today
    if (data.show_current_location && data.driver.current_lat && data.driver.current_lng) {
        bounds.push([data.driver.current_lat, data.driver.current_lng]);

        const driverMarker = L.marker([data.driver.current_lat, data.driver.current_lng], {
            icon: L.divIcon({
                className: 'driver-marker',
                html: '<div style="background: red; width: 20px; height: 20px; border-radius: 50%; border: 3px solid white;"></div>',
                iconSize: [20, 20]
            })
        }).addTo(map);

        driverMarker.bindPopup(`
            <strong>${data.driver.ho_ten}</strong><br>
            ${data.driver.bien_so_xe}<br>
            Cập nhật: ${data.driver.last_update}
        `);

        markers.push(driverMarker);
    }

    // Fit map to bounds
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
}
</script>
@endpush
@endsection
