@extends('layouts.driver')

@section('page_title', 'Dashboard')

@section('content')

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">
                <span class="title-icon" style="background: linear-gradient(135deg, #FF6B2B, #FF9A5C);">
                    <i class="bi bi-speedometer2"></i>
                </span>
                Dashboard
            </h1>
            <p class="page-subtitle mb-0">
                <i class="bi bi-calendar3 me-1"></i>
                {{ now()->locale('vi')->isoFormat('dddd, D/M/YYYY') }} ·
                Xin chào, <strong>{{ Auth::user()->name }}</strong>! 🚀
            </p>
        </div>

        <div class="d-flex gap-3 align-items-center flex-wrap">
            {{-- Date Range Filter Form --}}
            <form action="{{ route('driver.dashboard') }}" method="GET" class="m-0">
                <div class="input-group input-group-sm" style="border-radius:20px; overflow:hidden; border: 1px solid {{ $errors->any() ? '#dc3545' : '#E2E8F0' }}; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                    <span class="input-group-text bg-white border-0 text-muted" style="padding-left: 1rem;"><i class="bi bi-calendar-range"></i></span>
                    <input type="date" name="start_date" class="form-control border-0 text-center" 
                           value="{{ old('start_date', request('start_date', today()->format('Y-m-d'))) }}" title="Từ ngày" style="min-width: 120px; cursor: pointer;">
                    <span class="input-group-text bg-white border-0 text-muted px-1">-</span>
                    <input type="date" name="end_date" class="form-control border-0 text-center" 
                           value="{{ old('end_date', request('end_date', today()->format('Y-m-d'))) }}" title="Đến ngày" style="min-width: 120px; cursor: pointer;">
                    <button type="submit" class="btn btn-light border-0 px-3 fw-medium" style="background:#F7FAFC; color:#4A5568;"><i class="bi bi-funnel"></i> Lọc</button>
                </div>
                @if($errors->any())
                    <div class="text-danger text-end mt-1 fw-medium position-absolute" style="font-size: 0.75rem;"><i class="bi bi-exclamation-circle text-danger"></i> {{ $errors->first() }}</div>
                @endif
            </form>

            <span class="badge d-flex align-items-center gap-2 px-3 py-2 fs-6"
                  style="background:rgba(34,211,160,.12);color:#22d3a0;border:1px solid rgba(34,211,160,.25);border-radius:20px;font-weight:600; height:34px;">
                <span style="width:8px;height:8px;background:#22d3a0;border-radius:50%;display:inline-block;animation:pulse-dot 2s infinite;"></span>
                Đang trực tuyến
            </span>
        </div>
    </div>
</div>

{{-- ── STAT CARDS (Wallet + Earnings + YC4) ─────────────────────── --}}
<div class="row g-4 mb-4">

    {{-- Card 0 - Số dư đối soát --}}
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('driver.wallet.index') }}" class="text-decoration-none d-block">
            <div class="driver-stat-card" style="--card-color: {{ ($wallet->balance < 0) ? '#FF4D4D' : (($wallet->balance > 0) ? '#22d3a0' : '#4F8EF7') }}; --card-glow: rgba(79,142,247,.18);">
                <div class="stat-icon-wrap">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stat-body">
                    <div class="stat-label">Số dư đối soát</div>
                    <div class="stat-value" style="font-size:1.6rem;">
                        {{ number_format(abs($wallet->balance ?? 0), 0, ',', '.') }} đ
                    </div>
                    <div class="mt-2 d-flex flex-column gap-1">
                        <span class="badge align-self-start {{ ($wallet->balance < 0) ? 'bg-danger' : (($wallet->balance > 0) ? 'bg-success' : 'bg-primary') }}">
                            {{ $walletStatusLabel }}
                        </span>
                        <div class="stat-sub" style="line-height:1.35; align-items:flex-start;">
                            {{ $walletStatusHelp }}
                        </div>
                    </div>
                </div>
                <div class="stat-bg-icon"><i class="bi bi-wallet2"></i></div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #0EA5E9; --card-glow: rgba(14,165,233,.22);">
            <div class="stat-icon-wrap">
                <i class="bi bi-arrow-down-circle"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Còn phải nộp</div>
                <div class="stat-value" style="font-size:1.6rem;">
                    {{ number_format(($walletOutstandingAmount ?? 0), 0, ',', '.') }} đ
                </div>
                <div class="stat-sub">
                    <i class="bi bi-info-circle"></i>
                    Dựa trên số dư đối soát hiện tại
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-arrow-down-circle"></i></div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #22d3a0; --card-glow: rgba(34,211,160,.25);">
            <div class="stat-icon-wrap">
                <i class="bi bi-arrow-up-circle"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Đang dư</div>
                <div class="stat-value" style="font-size:1.6rem;">
                    {{ number_format(($walletSurplusAmount ?? 0), 0, ',', '.') }} đ
                </div>
                <div class="stat-sub">
                    <i class="bi bi-wallet2"></i>
                    Số dư sau đối soát hoặc thưởng
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-arrow-up-circle"></i></div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #A78BFA; --card-glow: rgba(167,139,250,.25);">
            <div class="stat-icon-wrap">
                <i class="bi bi-award"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Thưởng KPI hôm nay</div>
                <div class="stat-value" style="font-size:1.6rem;">
                    {{ number_format(($kpiToday ?? 0), 0, ',', '.') }} đ
                </div>
                <div class="stat-sub">
                    <i class="bi bi-calendar-check"></i> Thu nhập từ KPI
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-award"></i></div>
        </div>
    </div>


    {{-- Card 0.2 - Thu nhập tháng --}}
    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #FF6B2B; --card-glow: rgba(255,107,43,.25);">
            <div class="stat-icon-wrap">
                <i class="bi bi-calendar2-month"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Thu nhập tháng</div>
                <div class="stat-value" style="font-size:1.6rem;">
                    {{ number_format(($earningsMonth ?? 0), 0, ',', '.') }} đ
                </div>
                <div class="stat-sub">
                    <i class="bi bi-graph-up"></i> Tuần: {{ number_format(($earningsWeek ?? 0), 0, ',', '.') }} đ
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-calendar2-month"></i></div>
        </div>
    </div>

    {{-- Card 1 - Đơn hôm nay / Khoảng ngày --}}
    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #4F8EF7; --card-glow: rgba(79,142,247,.25);">
            <div class="stat-icon-wrap">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Đơn hoàn thành</div>
                <div class="stat-value">{{ $totalCompletedOrders ?? 0 }}</div>
                <div class="stat-sub" style="font-size: 0.75rem;">
                    <i class="bi bi-calendar-range"></i>
                    @php
                        $s = request('start_date', today()->format('Y-m-d'));
                        $e = request('end_date', today()->format('Y-m-d'));
                        if ($s == $e) {
                            echo \Carbon\Carbon::parse($s)->format('d/m/Y');
                        } else {
                            echo \Carbon\Carbon::parse($s)->format('d/m') . ' - ' . \Carbon\Carbon::parse($e)->format('d/m/Y');
                        }
                    @endphp
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>

    {{-- Card 2 - Đang giao --}}
    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #FF6B2B; --card-glow: rgba(255,107,43,.25);">
            <div class="stat-icon-wrap">
                <i class="bi bi-truck"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Đang giao</div>
                <div class="stat-value">{{ $dangGiao ?? 0 }}</div>
                <div class="stat-sub">
                    <i class="bi bi-lightning-charge"></i>
                    Đang xử lý trong kỳ
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-truck"></i></div>
        </div>
    </div>

    {{-- Card 3 - Đã hoàn thành --}}
    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #22d3a0; --card-glow: rgba(34,211,160,.25);">
            <div class="stat-icon-wrap">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Đã hoàn thành</div>
                <div class="stat-value">{{ $daHoanThanh ?? 0 }}</div>
                <div class="stat-sub">
                    <i class="bi bi-trophy"></i>
                    Thành công trong kỳ
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-check2-circle"></i></div>
        </div>
    </div>

    {{-- Card 4 - Còn lại (YC4) --}}
    <div class="col-xl-3 col-md-6">
        <div class="driver-stat-card" style="--card-color: #A78BFA; --card-glow: rgba(167,139,250,.25);">
            <div class="stat-icon-wrap">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="stat-body">
                <div class="stat-label">Tồn đọng / Còn lại</div>
                <div class="stat-value">{{ $conLai ?? 0 }}</div>
                <div class="stat-sub">
                    <i class="bi bi-clock"></i>
                    Tồn đọng đến cuối kỳ
                </div>
            </div>
            <div class="stat-bg-icon"><i class="bi bi-hourglass-split"></i></div>
        </div>
    </div>

</div>

{{-- ── QUICK ACTION & INFO ROW ──────────────────────────────── --}}
<div class="row g-4">

    {{-- Quick actions --}}
    <div class="col-lg-4">
        <div class="driver-card">
            <div class="driver-card-header">
                <i class="bi bi-lightning-charge-fill" style="color:#FF6B2B;"></i>
                Thao tác nhanh
            </div>
            <div class="driver-card-body">
                <a href="{{ route('driver.orders') }}" class="driver-quick-btn">
                    <span class="qbtn-icon" style="background:rgba(79,142,247,.12);color:#4F8EF7;">
                        <i class="bi bi-bag-check"></i>
                    </span>
                    <div>
                        <div class="qbtn-label">Xem đơn hàng</div>
                        <div class="qbtn-sub">Danh sách đơn được giao</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:#CBD5E0;"></i>
                </a>

                <a href="{{ route('driver.route') }}" class="driver-quick-btn">
                    <span class="qbtn-icon" style="background:rgba(255,107,43,.12);color:#FF6B2B;">
                        <i class="bi bi-map"></i>
                    </span>
                    <div>
                        <div class="qbtn-label">Xem bản đồ</div>
                        <div class="qbtn-sub">Lộ trình hôm nay</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:#CBD5E0;"></i>
                </a>

                <a href="{{ route('driver.route.today') }}" class="driver-quick-btn">
                    <span class="qbtn-icon" style="background:rgba(167,139,250,.12);color:#A78BFA;">
                        <i class="bi bi-geo-alt"></i>
                    </span>
                    <div>
                        <div class="qbtn-label">Lộ trình tối ưu</div>
                        <div class="qbtn-sub">Sắp xếp theo vị trí gần nhất</div>
                    </div>
                    <i class="bi bi-chevron-right ms-auto" style="color:#CBD5E0;"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Today summary --}}
    <div class="col-lg-8">
        <div class="driver-card">
            <div class="driver-card-header">
                <i class="bi bi-bar-chart-fill" style="color:#FF6B2B;"></i>
                Tổng kết hôm nay
            </div>
            <div class="driver-card-body">
                {{-- Progress bar --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span style="font-size:.85rem;font-weight:600;color:#2D3748;">Tiến độ hoàn thành</span>
                        @php
                            $total = ($tongDonHomNay ?? 0);
                            $done  = ($daHoanThanh ?? 0);
                            $pct   = $total > 0 ? round(($done / $total) * 100) : 0;
                        @endphp
                        <span style="font-size:.85rem;font-weight:700;color:#FF6B2B;">{{ $pct }}%</span>
                    </div>
                    <div class="progress" style="height:10px;border-radius:10px;background:#EDF2F7;">
                        <div class="progress-bar"
                             style="width:{{ $pct }}%;background:linear-gradient(90deg,#FF6B2B,#FF9A5C);border-radius:10px;transition:width 1s ease;">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2" style="font-size:.75rem;color:#A0AEC0;">
                        <span>{{ $daHoanThanh ?? 0 }} hoàn thành</span>
                        <span>{{ $conLai ?? 0 }} còn lại</span>
                    </div>
                </div>

                {{-- Stat grid --}}
                <div class="row g-3 mb-4">
                    <div class="col-3">
                        <div class="text-center p-3 rounded-3" style="background:#F7F9FF;border:1px solid #E2E8F0;">
                            <div style="font-size:1.6rem;font-weight:800;color:#4F8EF7;">{{ $tongDonHomNay ?? 0 }}</div>
                            <div style="font-size:.75rem;color:#718096;font-weight:500;">Tổng đơn</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center p-3 rounded-3" style="background:#FFF7F4;border:1px solid #FFE0D0;">
                            <div style="font-size:1.6rem;font-weight:800;color:#FF6B2B;">{{ $dangGiao ?? 0 }}</div>
                            <div style="font-size:.75rem;color:#718096;font-weight:500;">Đang giao</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center p-3 rounded-3" style="background:#F0FDF9;border:1px solid #C6F6E8;">
                            <div style="font-size:1.6rem;font-weight:800;color:#22d3a0;">{{ $daHoanThanh ?? 0 }}</div>
                            <div style="font-size:.75rem;color:#718096;font-weight:500;">Xong</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="text-center p-3 rounded-3" style="background:#FAF5FF;border:1px solid #E9D5FF;">
                            <div style="font-size:1.6rem;font-weight:800;color:#A78BFA;">{{ $conLai ?? 0 }}</div>
                            <div style="font-size:.75rem;color:#718096;font-weight:500;">Còn lại</div>
                        </div>
                    </div>
                </div>

                {{-- Đơn hàng gần nhất --}}
                @if(isset($donHangGanNhat) && count($donHangGanNhat) > 0)
                <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#A0AEC0;margin-bottom:.6rem;">
                    Đơn chưa giao xong
                </div>
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    @foreach($donHangGanNhat as $dh)
                    <a href="{{ route('driver.orders.show', $dh->id) }}"
                       style="display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border-radius:10px;text-decoration:none;color:inherit;border:1px solid #F0F4F8;transition:all .15s;"
                       onmouseover="this.style.background='#F7FAFC'" onmouseout="this.style.background='transparent'">
                        <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#4F8EF7,#667EEA);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr($dh->khachHang?->ten_khach ?? 'K', 0, 1)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.85rem;font-weight:600;color:#2D3748;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $dh->khachHang?->ten_khach ?? '—' }}</div>
                            <div style="font-size:.74rem;color:#A0AEC0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $dh->khachHang?->dia_chi ?? '—' }}</div>
                        </div>
                        @if($dh->khachHang?->latitude && $dh->khachHang?->longitude)
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $dh->khachHang->latitude }},{{ $dh->khachHang->longitude }}"
                           target="_blank"
                           onclick="event.stopPropagation()"
                           style="width:28px;height:28px;border-radius:7px;background:rgba(34,211,160,.1);color:#22d3a0;display:flex;align-items:center;justify-content:center;text-decoration:none;flex-shrink:0;"
                           title="Chỉ đường">
                            <i class="bi bi-send-fill" style="font-size:.7rem;"></i>
                        </a>
                        @endif
                        <i class="bi bi-chevron-right" style="color:#CBD5E0;font-size:.8rem;flex-shrink:0;"></i>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ── STAT CARDS ──────────────────────────── */
.driver-stat-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.1rem;
    position: relative;
    overflow: hidden;
    border: 1px solid #E2E8F0;
    box-shadow: 0 2px 12px rgba(0,0,0,.04);
    transition: all .25s;
}
.driver-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px var(--card-glow, rgba(0,0,0,.1));
    border-color: var(--card-color);
}
.stat-icon-wrap {
    width: 56px; height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, var(--card-color), color-mix(in srgb, var(--card-color) 70%, #fff));
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 6px 16px var(--card-glow, rgba(0,0,0,.15));
}
.stat-body { flex: 1; }
.stat-label {
    font-size: .78rem;
    font-weight: 600;
    color: #A0AEC0;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.stat-value {
    font-size: 2.2rem;
    font-weight: 800;
    color: #1A202C;
    line-height: 1.1;
    margin: .15rem 0;
}
.stat-sub {
    font-size: .78rem;
    color: #A0AEC0;
    display: flex; align-items: center; gap: .3rem;
}
.stat-bg-icon {
    position: absolute;
    right: -10px; bottom: -10px;
    font-size: 5rem;
    color: var(--card-color);
    opacity: .05;
    pointer-events: none;
}

/* ── CARD ──────────────────────────── */
.driver-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 2px 12px rgba(0,0,0,.04);
    overflow: hidden;
}
.driver-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #F0F4F8;
    font-size: .9rem;
    font-weight: 700;
    color: #2D3748;
    display: flex; align-items: center; gap: .5rem;
}
.driver-card-body { padding: 1.25rem; }

/* ── QUICK BUTTONS ──────────────────────────── */
.driver-quick-btn {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .85rem .75rem;
    border-radius: 12px;
    text-decoration: none;
    color: #2D3748;
    transition: all .2s;
    margin-bottom: .5rem;
}
.driver-quick-btn:last-child { margin-bottom: 0; }
.driver-quick-btn:hover { background: #F7FAFC; color: #1A202C; }
.qbtn-icon {
    width: 42px; height: 42px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
}
.qbtn-label { font-size: .88rem; font-weight: 600; }
.qbtn-sub   { font-size: .75rem; color: #A0AEC0; }
</style>
@endpush

@push('scripts')
<script>
    // Theo dõi và gửi vị trí người dùng (Tài xế) lên Server mỗi 10 giây
    function updateLocation() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                fetch("{{ route('driver.api.driver.update_location') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lat: lat, lng: lng })
                })
                .then(r => r.json())
                .then(data => { if(data.success) console.log('Đã cập nhật vị trí:', lat, lng); })
                .catch(err => console.warn('Lỗi cập nhật vị trí:', err));
            }, err => console.warn('Geolocation error:', err.message), {
                enableHighAccuracy: true, timeout: 5000, maximumAge: 0
            });
        }
    }
    updateLocation();
    setInterval(updateLocation, 10000);
</script>
@endpush
