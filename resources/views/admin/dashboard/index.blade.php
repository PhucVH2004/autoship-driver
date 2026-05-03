{{-- admin/dashboard/index.blade.php --}}
@extends('layouts.admin')

@section('page_title', 'Dashboard')

@push('extra_css')
<style>
    /* Màu gradient cho từng stat card */
    .sc-blue   { background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%); color:#fff; }
    .sc-amber  { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color:#fff; }
    .sc-green  { background: linear-gradient(135deg, #10b981 0%, #047857 100%); color:#fff; }
    .sc-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color:#fff; }

    .stat-card .stat-val,
    .stat-card .stat-lbl,
    .stat-card .stat-trend { color: rgba(255,255,255,.9) !important; }
    .stat-card .stat-lbl   { color: rgba(255,255,255,.7) !important; }
    .stat-card .stat-icon-wrap {
        width: 52px; height: 52px;
        background: rgba(255,255,255,.18);
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .stat-card .badge-chip {
        background: rgba(255,255,255,.22);
        color: #fff;
        font-size: .72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-block;
    }

    /* Bảng recent orders */
    .avatar-sm {
        width: 33px; height: 33px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .78rem;
        flex-shrink: 0;
    }

    /* Quick stats row bên dưới chart */
    .mini-stat {
        background: #fff;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .mini-stat .ms-icon {
        width: 42px; height: 42px;
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
    }
    .mini-stat .ms-val   { font-size: 1.3rem; font-weight: 800; color: #0f172a; }
    .mini-stat .ms-label { font-size: .75rem; color: #64748b; font-weight: 500; }
</style>
@endpush

@section('content')

{{-- ── HEADER ── --}}
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="page-title">
            <i class="fa-solid fa-gauge-high me-2 text-primary" style="font-size:1.2rem;"></i>
            Dashboard
        </h1>
        <p class="page-sub">
            Xin chào, <strong>@auth{{ auth()->user()->name }}@else Admin @endauth</strong>!
            Tổng quan hoạt động giao hàng hôm nay.
        </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="text-muted" style="font-size:.82rem;">
            <i class="fa-regular fa-calendar me-1"></i>{{ now()->format('l, d/m/Y') }}
        </span>
        <button class="btn btn-gradient btn-sm">
            <i class="fa-solid fa-rotate me-1"></i> Làm mới
        </button>
    </div>
</div>

{{-- ── 4 STAT CARDS — dữ liệu thật từ $thong_ke ── --}}
<div class="row g-3 mb-4">

    {{-- Card 1: Tổng đơn hôm nay --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card sc-blue h-100">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-lbl">Tổng đơn hôm nay</div>
                        <div class="stat-val mt-1">{{ $thong_ke['tong_don_hom_nay'] }}</div>
                    </div>
                    <div class="stat-icon-wrap"><i class="fa-solid fa-box-open"></i></div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge-chip">Hôm nay</span>
                    <span class="stat-trend" style="font-size:.75rem;">{{ now()->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 2: Đơn đang giao --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card sc-amber h-100">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-lbl">Đơn đang giao</div>
                        <div class="stat-val mt-1">{{ $thong_ke['don_dang_giao'] }}</div>
                    </div>
                    <div class="stat-icon-wrap"><i class="fa-solid fa-truck-fast"></i></div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge-chip">Đang xử lý</span>
                    <span class="stat-trend" style="font-size:.75rem;">{{ $thong_ke['tai_xe_hoat_dong'] }} tài xế chạy</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3: Đơn hoàn thành --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card sc-green h-100">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-lbl">Đơn hoàn thành hôm nay</div>
                        <div class="stat-val mt-1">{{ $thong_ke['don_hoan_thanh'] }}</div>
                    </div>
                    <div class="stat-icon-wrap"><i class="fa-solid fa-circle-check"></i></div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge-chip">Thành công</span>
                    <span class="stat-trend" style="font-size:.75rem;">Cập nhật liên tục</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 4: Tài xế hoạt động --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card sc-purple h-100">
            <div class="card-body d-flex flex-column gap-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-lbl">Tài xế hoạt động</div>
                        <div class="stat-val mt-1">{{ $thong_ke['tai_xe_hoat_dong'] }}</div>
                    </div>
                    <div class="stat-icon-wrap"><i class="fa-solid fa-id-card"></i></div>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge-chip">Rảnh + Đang giao</span>
                    <span class="stat-trend" style="font-size:.75rem;">Theo thời gian thực</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── FINANCE SUMMARY (Logistics Finance) ───────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="mini-stat">
            <div class="ms-icon" style="background:#dcfce7; color:#16a34a;">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div>
                <div class="ms-val">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }} đ</div>
                <div class="ms-label">Tổng doanh thu hệ thống (đơn hoàn thành)</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="mini-stat">
            <div class="ms-icon" style="background:#dbeafe; color:#2563eb;">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <div class="ms-val">{{ number_format($totalPlatformFee ?? 0, 0, ',', '.') }} đ</div>
                <div class="ms-label">Tổng phí platform (sau chia %)</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="mini-stat">
            <div class="ms-icon" style="background:#fef9c3; color:#d97706;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="ms-val">{{ $totalCompletedOrders ?? 0 }}</div>
                <div class="ms-label">Tổng số đơn hoàn thành</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="mini-stat">
            <div class="ms-icon" style="background:#ede9fe; color:#7c3aed;">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div>
                <div class="ms-val">{{ number_format($totalDriverPaid ?? 0, 0, ',', '.') }} đ</div>
                <div class="ms-label">Tổng tiền trả cho tài xế (thực nhận)</div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="mini-stat">
            <div class="ms-icon" style="background:#fee2e2; color:#dc2626;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <div class="ms-val">{{ number_format($totalCodFee ?? 0, 0, ',', '.') }} đ</div>
                <div class="ms-label">Tổng phí COD (1%)</div>
            </div>
        </div>
    </div>
</div>

{{-- ── BIỂU ĐỒ + MINI STATS ── --}}
<div class="row g-3 mb-4">

    {{-- Chart.js: Đơn hàng theo ngày --}}
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="d-flex align-items-start justify-content-between mb-1">
                <div>
                    <div class="chart-card-title">
                        <i class="fa-solid fa-chart-line me-2 text-primary"></i>Đơn hàng theo ngày
                    </div>
                    <div class="chart-card-sub">7 ngày gần nhất — cập nhật lúc {{ now()->format('H:i') }}</div>
                </div>
                {{-- Legend --}}
                <div class="d-flex gap-3" style="font-size:.75rem; color:#64748b;">
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#3b82f6;margin-right:5px;"></span>Tổng đơn</span>
                    <span><span style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#10b981;margin-right:5px;"></span>Hoàn thành</span>
                </div>
            </div>
            <canvas id="chart-orders" height="105"></canvas>
        </div>
    </div>

    {{-- Cột bên phải: Top drivers --}}
    <div class="col-lg-4 d-flex flex-column gap-3">

        <div class="mini-stat" style="flex-direction:column; align-items:stretch;">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-2">
                    <div class="ms-icon" style="background:#ede9fe; color:#7c3aed;">
                        <i class="fa-solid fa-ranking-star"></i>
                    </div>
                    <div>
                        <div class="ms-val" style="font-size:1rem;">Top drivers</div>
                        <div class="ms-label">Thu nhập theo tài xế</div>
                    </div>
                </div>
            </div>
            <div class="mt-3" style="display:flex; flex-direction:column; gap:10px;">
                @forelse(($topDrivers ?? []) as $row)
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:#7c3aed1a; color:#7c3aed;">
                                {{ strtoupper(mb_substr($row->taiXe?->ho_ten ?? '?', 0, 1)) }}
                            </div>
                            <div style="min-width:0;">
                                <div style="font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    {{ $row->taiXe?->ho_ten ?? '—' }}
                                </div>
                                <div style="font-size:.75rem; color:#64748b;">
                                    {{ (int) ($row->total_completed ?? 0) }} đơn hoàn thành
                                </div>
                            </div>
                        </div>
                        <div style="font-weight:800; color:#16a34a;">
                            {{ number_format((float) ($row->total_income ?? 0), 0, ',', '.') }} đ
                        </div>
                    </div>
                @empty
                    <div class="text-muted" style="font-size:.85rem;">Chưa có dữ liệu thu nhập.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>

{{-- ── BẢNG ĐƠN HÀNG GẦN ĐÂY ── --}}
<div class="data-card">
    <div class="data-card-header">
        <h6><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Đơn hàng gần đây</h6>
        <a href="{{ route('admin.don_hang.index') }}" class="btn btn-gradient btn-sm">
            Xem tất cả <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    {{-- Bảng đơn hàng gần đây — data thật từ $donHangGanDay --}}
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tài xế phụ trách</th>
                    <th>Trạng thái</th>
                    <th>Thời gian tạo</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @if(count($donHangGanDay) > 0)
                @foreach($donHangGanDay as $dh)
                <tr>
                    <td>
                        <span style="font-weight:700; font-family:monospace; font-size:.88rem; color:#1e40af;">
                            {{ $dh->ma_don }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm" style="background:#3b82f61a; color:#3b82f6;">
                                {{ strtoupper(mb_substr($dh->khachHang->ten_khach ?? '?', 0, 1)) }}
                            </div>
                            <span style="font-weight:500;">{{ $dh->khachHang->ten_khach ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        @if($dh->taiXe)
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle" style="font-size:.45rem; color:#22c55e;"></i>
                                {{ $dh->taiXe->ho_ten }}
                            </span>
                        @else
                            <span class="text-muted" style="font-size:.85rem;">Chưa phân công</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $dh->trangThai?->css_class ?? '' }}">
                            {{ $dh->trangThai?->ten_trang_thai ?? '—' }}
                        </span>
                    </td>
                    <td style="color:#94a3b8; font-size:.82rem;">
                        <i class="fa-regular fa-clock me-1"></i>{{ $dh->created_at->format('H:i — d/m') }}
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.don_hang.show', $dh) }}"
                               class="btn btn-sm btn-light rounded-2" title="Chi tiết"
                               style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;">
                                <i class="fa-regular fa-eye text-primary"></i>
                            </a>
                            <a href="{{ route('admin.don_hang.edit', $dh) }}"
                               class="btn btn-sm btn-light rounded-2" title="Sửa"
                               style="width:32px;height:32px;padding:0;display:flex;align-items:center;justify-content:center;">
                                <i class="fa-regular fa-pen-to-square text-warning"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>Chưa có đơn hàng nào.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('extra_js')
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── DỮ LIỆU BIỂU ĐỒ — từ $chartData (controller query 7 ngày) ──
const chartData   = @json($chartData);
const chartLabels = chartData.map(d => d.label);
const totalOrders = chartData.map(d => d.tong);
const doneOrders  = chartData.map(d => d.hoan_thanh);

const ctx = document.getElementById('chart-orders').getContext('2d');

// Gradient fill cho đường "Tổng đơn"
const gradBlue = ctx.createLinearGradient(0, 0, 0, 260);
gradBlue.addColorStop(0, 'rgba(59,130,246,.25)');
gradBlue.addColorStop(1, 'rgba(59,130,246,0)');

// Gradient fill cho đường "Hoàn thành"
const gradGreen = ctx.createLinearGradient(0, 0, 0, 260);
gradGreen.addColorStop(0, 'rgba(16,185,129,.2)');
gradGreen.addColorStop(1, 'rgba(16,185,129,0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [
            {
                label       : 'Tổng đơn',
                data        : totalOrders,
                borderColor : '#3b82f6',
                backgroundColor: gradBlue,
                borderWidth : 2.5,
                pointRadius : 4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension     : 0.4,
                fill        : true,
            },
            {
                label       : 'Hoàn thành',
                data        : doneOrders,
                borderColor : '#10b981',
                backgroundColor: gradGreen,
                borderWidth : 2.5,
                pointRadius : 4,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                tension     : 0.4,
                fill        : true,
            }
        ]
    },
    options: {
        responsive  : true,
        interaction : { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },  // Dùng legend custom ở trên
            tooltip: {
                backgroundColor : '#0f172a',
                titleFont       : { size: 12, weight: '600' },
                bodyFont        : { size: 12 },
                padding         : 12,
                cornerRadius    : 10,
                callbacks: {
                    // Thêm ký tự " đơn" vào tooltip
                    label: (ctx) => `  ${ctx.dataset.label}: ${ctx.parsed.y} đơn`,
                }
            }
        },
        scales: {
            x: {
                grid  : { display: false },
                border: { display: false },
                ticks : { font: { size: 11 }, color: '#94a3b8' }
            },
            y: {
                grid  : { color: '#f1f5f9' },
                border: { display: false, dash: [4,4] },
                ticks : { font: { size: 11 }, color: '#94a3b8', stepSize: 10 },
                min   : 0,
            }
        }
    }
});
</script>
@endpush
