@extends('layouts.admin')

@section('page_title', 'Đối soát tài chính')

@push('extra_css')
<style>
    .kpi-card {
        border: none;
        border-radius: 14px;
        box-shadow: var(--card-shadow);
        color: #fff;
        overflow: hidden;
    }
    .kpi-card .card-body {
        padding: 18px 20px;
    }
    .kpi-label {
        font-size: .74rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: .05em;
        opacity: .9;
        margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 800;
        line-height: 1.1;
    }
    .kpi-meta {
        margin-top: 8px;
        font-size: .8rem;
        opacity: .95;
    }
    .kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: rgba(255,255,255,.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    .kpi-blue { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
    .kpi-green { background: linear-gradient(135deg, #16a34a, #047857); }
    .kpi-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .kpi-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .kpi-purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    .kpi-cyan { background: linear-gradient(135deg, #06b6d4, #0284c7); }
    .kpi-rose { background: linear-gradient(135deg, #fb7185, #e11d48); }
</style>
@endpush

@section('content')
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="page-title">
            <i class="fa-solid fa-scale-balanced me-2 text-primary" style="font-size:1.2rem;"></i>
            Đối soát tài chính
        </h1>
        <p class="page-sub">
            Theo dõi doanh thu theo ngày/tháng/năm giữa Admin, Shop và Tài xế.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.settlement.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-file-csv me-1"></i>Xuất CSV
        </a>
        <form method="POST" action="{{ route('admin.settlement.confirm_driver_repayment') }}">
            @csrf
            <input type="hidden" name="filter_type" value="{{ $filterType }}">
            <input type="hidden" name="date" value="{{ $filterType === 'day' ? $filterValue : '' }}">
            <input type="hidden" name="month" value="{{ $filterType === 'month' ? $filterValue : '' }}">
            <input type="hidden" name="year" value="{{ $filterType === 'year' ? $filterValue : '' }}">
            <input type="hidden" name="shop_id" value="{{ $selectedShopId }}">
            <input type="hidden" name="driver_id" value="{{ $selectedDriverId }}">
            <button type="submit" class="btn btn-outline-warning">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i>Xác nhận tài xế đã nộp
            </button>
        </form>
        <form method="POST" action="{{ route('admin.settlement.transfer') }}">
            @csrf
            <input type="hidden" name="filter_type" value="{{ $filterType }}">
            <input type="hidden" name="date" value="{{ $filterType === 'day' ? $filterValue : '' }}">
            <input type="hidden" name="month" value="{{ $filterType === 'month' ? $filterValue : '' }}">
            <input type="hidden" name="year" value="{{ $filterType === 'year' ? $filterValue : '' }}">
            <input type="hidden" name="shop_id" value="{{ $selectedShopId }}">
            <input type="hidden" name="driver_id" value="{{ $selectedDriverId }}">
            <button type="submit" class="btn btn-outline-success">
                <i class="fa-solid fa-money-check-dollar me-1"></i>Đánh dấu đã chuyển khoản
            </button>
        </form>
        <form method="POST" action="{{ route('admin.settlement.close') }}">
            @csrf
            <input type="hidden" name="filter_type" value="{{ $filterType }}">
            <input type="hidden" name="date" value="{{ $filterType === 'day' ? $filterValue : '' }}">
            <input type="hidden" name="month" value="{{ $filterType === 'month' ? $filterValue : '' }}">
            <input type="hidden" name="year" value="{{ $filterType === 'year' ? $filterValue : '' }}">
            <input type="hidden" name="shop_id" value="{{ $selectedShopId }}">
            <input type="hidden" name="driver_id" value="{{ $selectedDriverId }}">
            <input type="hidden" name="total_completed_orders" value="{{ (float) ($overview['total_completed_orders'] ?? 0) }}">
            <input type="hidden" name="total_cod" value="{{ (float) ($overview['total_cod'] ?? 0) }}">
            <input type="hidden" name="total_shipping_fee" value="{{ (float) ($overview['total_shipping_fee'] ?? 0) }}">
            <input type="hidden" name="total_platform_fee" value="{{ (float) ($overview['total_platform_fee'] ?? 0) }}">
            <input type="hidden" name="total_cod_fee" value="{{ (float) ($overview['total_cod_fee'] ?? 0) }}">
            <input type="hidden" name="total_driver_income" value="{{ (float) ($overview['total_driver_income'] ?? 0) }}">
            <input type="hidden" name="total_shop_net" value="{{ (float) ($overview['total_shop_net'] ?? 0) }}">
            <input type="hidden" name="total_admin_revenue" value="{{ (float) ($overview['total_admin_revenue'] ?? 0) }}">
            <button type="submit" class="btn btn-gradient">
                <i class="fa-solid fa-check me-1"></i>Chốt kỳ đối soát
            </button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3">
        <i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
    </div>
@endif

<div class="data-card mb-4">
    <div class="data-card-header">
        <h6><i class="fa-solid fa-list-check me-2 text-primary"></i>Admin cần làm gì?</h6>
    </div>
    <div class="p-3 p-md-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-bold mb-1">Bước 1 - Thu tiền từ tài xế</div>
                    <div class="text-muted small">Nếu đơn đang "Chờ tài xế nộp", bấm <strong>Xác nhận tài xế đã nộp</strong> để ghi nhận `cod_repayment`.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-bold mb-1">Bước 2 - Chốt kỳ đối soát</div>
                    <div class="text-muted small">Khi tiền đã đủ, bấm <strong>Chốt kỳ đối soát</strong> để khóa số liệu kỳ hiện tại (status `closed`).</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded-3 p-3 h-100">
                    <div class="fw-bold mb-1">Bước 3 - Chuyển khoản cho shop</div>
                    <div class="text-muted small">Sau khi thanh toán ngân hàng, bấm <strong>Đánh dấu đã chuyển khoản</strong> để hoàn tất (status `transferred`).</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="data-card mb-4">
    <div class="data-card-header">
        <h6><i class="fa-solid fa-filter me-2 text-primary"></i>Bộ lọc thời gian</h6>
    </div>
    <div class="p-3 p-md-4">
        <form method="GET" action="{{ route('admin.settlement.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Kiểu lọc</label>
                <select name="filter_type" id="filterType" class="form-select">
                    <option value="day" {{ $filterType === 'day' ? 'selected' : '' }}>Theo ngày</option>
                    <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>Theo tháng</option>
                    <option value="year" {{ $filterType === 'year' ? 'selected' : '' }}>Theo năm</option>
                </select>
            </div>
            <div class="col-md-3" id="dayInput">
                <label class="form-label fw-semibold">Ngày</label>
                <input type="date" name="date" class="form-control" value="{{ $filterType === 'day' ? $filterValue : now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3" id="monthInput">
                <label class="form-label fw-semibold">Tháng</label>
                <input type="month" name="month" class="form-control" value="{{ $filterType === 'month' ? $filterValue : now()->format('Y-m') }}">
            </div>
            <div class="col-md-3" id="yearInput">
                <label class="form-label fw-semibold">Năm</label>
                <input type="number" min="2020" max="{{ now()->year + 1 }}" name="year" class="form-control" value="{{ $filterType === 'year' ? $filterValue : now()->year }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Shop</label>
                <select name="shop_id" class="form-select">
                    <option value="">Tất cả shop</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ (int) $selectedShopId === (int) $shop->id ? 'selected' : '' }}>
                            {{ $shop->ten_shop }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Tài xế</label>
                <select name="driver_id" class="form-select">
                    <option value="">Tất cả tài xế</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ (int) $selectedDriverId === (int) $driver->id ? 'selected' : '' }}>
                            {{ $driver->ho_ten }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-gradient w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Lọc dữ liệu
                </button>
            </div>
        </form>
        <div class="text-muted mt-3" style="font-size:.84rem;">
            Khoảng dữ liệu: <strong>{{ \Illuminate\Support\Carbon::parse($startAt)->format('d/m/Y H:i') }}</strong>
            đến
            <strong>{{ \Illuminate\Support\Carbon::parse($endAt)->format('d/m/Y H:i') }}</strong>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card kpi-card kpi-blue h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Đơn hoàn thành</div>
                        <div class="kpi-value">{{ number_format((float) ($overview['total_completed_orders'] ?? 0), 0, ',', '.') }}</div>
                        <div class="kpi-meta">Trong kỳ lọc</div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid fa-cart-flatbed"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card kpi-card kpi-green h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Shop thực nhận</div>
                        <div class="kpi-value">{{ number_format((float) ($overview['total_shop_net'] ?? 0), 0, ',', '.') }} đ</div>
                        <div class="kpi-meta">Sau khi trừ phí</div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid fa-building"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card kpi-card kpi-amber h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Tài xế thực nhận</div>
                        <div class="kpi-value">{{ number_format((float) ($overview['total_driver_income'] ?? 0), 0, ',', '.') }} đ</div>
                        <div class="kpi-meta">Thu nhập giao hàng</div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid fa-user-tie"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card kpi-card kpi-red h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Doanh thu Admin</div>
                        <div class="kpi-value">{{ number_format((float) ($overview['total_admin_revenue'] ?? 0), 0, ',', '.') }} đ</div>
                        <div class="kpi-meta">Platform + COD fee</div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid fa-landmark"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card kpi-card kpi-purple h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Cần chuyển Shop</div>
                        <div class="kpi-value">{{ number_format((float) ($walletSummary['shop_pending_settlement'] ?? 0), 0, ',', '.') }} đ</div>
                        <div class="kpi-meta">Số dư chờ đối soát</div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid fa-store"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card kpi-rose h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Tài xế còn phải nộp</div>
                        <div class="kpi-value">{{ number_format((float) ($walletSummary['driver_outstanding'] ?? 0), 0, ',', '.') }} đ</div>
                        <div class="kpi-meta">Công nợ COD hiện tại</div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card kpi-card kpi-cyan h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="kpi-label">Ví tài xế đang dư</div>
                        <div class="kpi-value">{{ number_format((float) ($walletSummary['driver_surplus'] ?? 0), 0, ',', '.') }} đ</div>
                        <div class="kpi-meta">Số dư dương toàn hệ thống</div>
                    </div>
                    <div class="kpi-icon"><i class="fa-solid fa-circle-check"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-6">
        <div class="data-card h-100">
            <div class="data-card-header">
                <h6><i class="fa-solid fa-shop me-2 text-primary"></i>Doanh thu từng Shop</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Shop</th>
                            <th class="text-end">Đơn HT</th>
                            <th class="text-end">COD</th>
                            <th class="text-end">Phí</th>
                            <th class="text-end">Thực nhận</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shopStats as $row)
                            <tr>
                                <td>{{ $row->shop_name }}</td>
                                <td class="text-end">{{ number_format((float) $row->completed_orders, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $row->cod_total, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $row->shipping_total + (float) $row->cod_fee_total, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format((float) $row->shop_net, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu shop trong kỳ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="data-card h-100">
            <div class="data-card-header">
                <h6><i class="fa-solid fa-id-card me-2 text-primary"></i>Doanh thu từng Tài xế</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tài xế</th>
                            <th class="text-end">Đơn HT</th>
                            <th class="text-end">Phí giao</th>
                            <th class="text-end">Thực nhận</th>
                            <th class="text-end">Giữ tiền mặt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($driverStats as $row)
                            <tr>
                                <td>{{ $row->driver_name }}</td>
                                <td class="text-end">{{ number_format((float) $row->completed_orders, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $row->delivery_fee_total, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-success">{{ number_format((float) $row->driver_income_total, 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $row->cash_holding_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Không có dữ liệu tài xế trong kỳ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="data-card mb-4">
    <div class="data-card-header">
        <h6><i class="fa-solid fa-receipt me-2 text-primary"></i>Hàng đợi đối soát theo đơn</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Shop</th>
                    <th>Tài xế</th>
                    <th class="text-end">Đã thu</th>
                    <th class="text-end">Đã nộp</th>
                    <th class="text-end">Còn phải nộp</th>
                    <th class="text-end">Shop thực nhận</th>
                    <th class="text-end">Đã chuyển</th>
                    <th>Trạng thái</th>
                    <th>Hành động tiếp theo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($settlementQueue as $row)
                    <tr>
                        <td class="fw-bold">{{ $row->ma_don }}</td>
                        <td>{{ $row->shop_name }}</td>
                        <td>{{ $row->driver_name }}</td>
                        <td class="text-end">{{ number_format((float) $row->target_collection, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $row->repaid_amount, 0, ',', '.') }}</td>
                        <td class="text-end {{ (float) $row->pending_driver > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                            {{ number_format((float) $row->pending_driver, 0, ',', '.') }}
                        </td>
                        <td class="text-end">{{ number_format((float) $row->expected_shop_payout, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) $row->shop_paid_amount, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $badgeClass = match($row->status_tone) {
                                    'warning' => 'bg-warning text-dark',
                                    'info' => 'bg-info text-dark',
                                    'primary' => 'bg-primary',
                                    default => 'bg-success',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $row->status_label }}</span>
                        </td>
                        <td>
                            @if((float) $row->pending_driver > 0.01)
                                <form method="POST" action="{{ route('admin.settlement.confirm_driver_repayment') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="filter_type" value="{{ $filterType }}">
                                    <input type="hidden" name="date" value="{{ $filterType === 'day' ? $filterValue : '' }}">
                                    <input type="hidden" name="month" value="{{ $filterType === 'month' ? $filterValue : '' }}">
                                    <input type="hidden" name="year" value="{{ $filterType === 'year' ? $filterValue : '' }}">
                                    <input type="hidden" name="shop_id" value="{{ $selectedShopId }}">
                                    <input type="hidden" name="driver_id" value="{{ $selectedDriverId }}">
                                    <input type="hidden" name="order_id" value="{{ $row->id }}">
                                    <button class="btn btn-sm btn-outline-warning">Xác nhận nộp</button>
                                </form>
                            @elseif((int) $row->settlement_stage < 1)
                                <form method="POST" action="{{ route('admin.settlement.close') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="filter_type" value="{{ $filterType }}">
                                    <input type="hidden" name="date" value="{{ $filterType === 'day' ? $filterValue : '' }}">
                                    <input type="hidden" name="month" value="{{ $filterType === 'month' ? $filterValue : '' }}">
                                    <input type="hidden" name="year" value="{{ $filterType === 'year' ? $filterValue : '' }}">
                                    <input type="hidden" name="shop_id" value="{{ $selectedShopId }}">
                                    <input type="hidden" name="driver_id" value="{{ $selectedDriverId }}">
                                    <input type="hidden" name="order_id" value="{{ $row->id }}">
                                    <input type="hidden" name="total_completed_orders" value="{{ (float) ($overview['total_completed_orders'] ?? 0) }}">
                                    <input type="hidden" name="total_cod" value="{{ (float) ($overview['total_cod'] ?? 0) }}">
                                    <input type="hidden" name="total_shipping_fee" value="{{ (float) ($overview['total_shipping_fee'] ?? 0) }}">
                                    <input type="hidden" name="total_platform_fee" value="{{ (float) ($overview['total_platform_fee'] ?? 0) }}">
                                    <input type="hidden" name="total_cod_fee" value="{{ (float) ($overview['total_cod_fee'] ?? 0) }}">
                                    <input type="hidden" name="total_driver_income" value="{{ (float) ($overview['total_driver_income'] ?? 0) }}">
                                    <input type="hidden" name="total_shop_net" value="{{ (float) ($overview['total_shop_net'] ?? 0) }}">
                                    <input type="hidden" name="total_admin_revenue" value="{{ (float) ($overview['total_admin_revenue'] ?? 0) }}">
                                    <button class="btn btn-sm btn-outline-primary">Chốt đơn</button>
                                </form>
                            @elseif((int) $row->settlement_stage < 2 || (float) $row->pending_transfer > 0.01)
                                <form method="POST" action="{{ route('admin.settlement.transfer') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="filter_type" value="{{ $filterType }}">
                                    <input type="hidden" name="date" value="{{ $filterType === 'day' ? $filterValue : '' }}">
                                    <input type="hidden" name="month" value="{{ $filterType === 'month' ? $filterValue : '' }}">
                                    <input type="hidden" name="year" value="{{ $filterType === 'year' ? $filterValue : '' }}">
                                    <input type="hidden" name="shop_id" value="{{ $selectedShopId }}">
                                    <input type="hidden" name="driver_id" value="{{ $selectedDriverId }}">
                                    <input type="hidden" name="order_id" value="{{ $row->id }}">
                                    <button class="btn btn-sm btn-outline-success">Đánh dấu chuyển khoản</button>
                                </form>
                            @else
                                <span class="text-success small fw-semibold">Hoàn tất</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">Không có đơn cần đối soát trong kỳ lọc.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="data-card mb-4">
    <div class="data-card-header">
        <h6><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Lịch sử chốt đối soát</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Thời gian chốt</th>
                    <th>Kỳ lọc</th>
                    <th class="text-end">Đơn HT</th>
                    <th class="text-end">Shop thực nhận</th>
                    <th class="text-end">TX thực nhận</th>
                    <th class="text-end">Admin doanh thu</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentSnapshots as $snapshot)
                    <tr>
                        <td>{{ $snapshot->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ strtoupper($snapshot->filter_type) }} - {{ $snapshot->filter_value }}</td>
                        <td class="text-end">{{ number_format((float) data_get($snapshot->overview, 'total_completed_orders', 0), 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) data_get($snapshot->overview, 'total_shop_net', 0), 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format((float) data_get($snapshot->overview, 'total_driver_income', 0), 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">{{ number_format((float) data_get($snapshot->overview, 'total_admin_revenue', 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Chưa có kỳ đối soát được chốt.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('extra_js')
<script>
    const filterTypeSelect = document.getElementById('filterType');
    const dayInput = document.getElementById('dayInput');
    const monthInput = document.getElementById('monthInput');
    const yearInput = document.getElementById('yearInput');

    function syncFilterInputs() {
        const type = filterTypeSelect.value;
        dayInput.style.display = type === 'day' ? '' : 'none';
        monthInput.style.display = type === 'month' ? '' : 'none';
        yearInput.style.display = type === 'year' ? '' : 'none';
    }

    filterTypeSelect.addEventListener('change', syncFilterInputs);
    syncFilterInputs();
</script>
@endpush
