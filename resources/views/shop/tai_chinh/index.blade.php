@extends('layouts.shop')
@section('page_title', 'Tài chính & Đối soát')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-800 fs-4 mb-1">
            <i class="bi bi-wallet2" style="color:#6C63FF;"></i> Tài chính & Đối soát
        </h1>
        <p class="text-muted mb-0">Lịch sử thu và chi COD của Shop.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary btn-sm" href="{{ route('shop.tai_chinh.index', array_merge(request()->query(), ['export' => 'csv'])) }}">
            <i class="bi bi-filetype-csv me-1"></i>Xuất CSV
        </a>
        <a class="btn btn-outline-success btn-sm" href="{{ route('shop.tai_chinh.index', array_merge(request()->query(), ['export' => 'excel'])) }}">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Xuất Excel
        </a>
    </div>
</div>

{{-- Balance card --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="bg-white rounded-4 shadow-sm p-4 text-center">
            <div class="fs-6 text-muted mb-1 fw-600">Số dư ví COD</div>
            <div class="fs-2 fw-800 {{ $codBalance >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format(abs($codBalance), 0, ',', '.') }}đ
            </div>
            <span class="badge {{ $codBalance >= 0 ? 'bg-success' : 'bg-danger' }} mt-2">
                {{ $codBalance >= 0 ? 'Chờ nhận' : 'Đang nợ' }}
            </span>
            <p class="text-muted small mt-3 mb-0">
                Liên hệ Admin để đối soát và chuyển khoản ngân hàng.
            </p>
        </div>
    </div>
    <div class="col-md-8">
        <div class="bg-white rounded-4 shadow-sm p-4">
            <h6 class="fw-700 mb-1">Thông tin ngân hàng</h6>
            <p class="text-muted small mb-3">Được dùng khi Admin đối soát và chuyển khoản.</p>
            <div class="row g-2 text-sm">
                <div class="col-6">
                    <span class="text-muted">Ngân hàng:</span>
                    <strong class="ms-2">{{ $shop?->bank_name ?? '—' }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">STK:</span>
                    <strong class="ms-2">{{ $shop?->bank_account_number ?? '—' }}</strong>
                </div>
                <div class="col-12">
                    <span class="text-muted">Chủ TK:</span>
                    <strong class="ms-2">{{ $shop?->bank_account_name ?? '—' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter + summary --}}
<div class="bg-white rounded-4 shadow-sm p-4 mb-4">
    <form method="GET" action="{{ route('shop.tai_chinh.index') }}" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label small fw-700">Từ ngày</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ optional($filters['date_from'])->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Đến ngày</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ optional($filters['date_to'])->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Loại giao dịch</label>
            <select name="reference_type" class="form-select form-select-sm">
                <option value="">Tất cả</option>
                <option value="cod_payment" {{ $filters['reference_type'] === 'cod_payment' ? 'selected' : '' }}>COD thu hộ</option>
                <option value="service_fees" {{ $filters['reference_type'] === 'service_fees' ? 'selected' : '' }}>Phí dịch vụ</option>
                <option value="reconciliation" {{ $filters['reference_type'] === 'reconciliation' ? 'selected' : '' }}>Đối soát</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Trạng thái đối soát</label>
            <select name="settlement_status" class="form-select form-select-sm">
                <option value="">Tất cả</option>
                <option value="pending" {{ $filters['settlement_status'] === 'pending' ? 'selected' : '' }}>Chưa đối soát</option>
                <option value="closed" {{ $filters['settlement_status'] === 'closed' ? 'selected' : '' }}>Đã chốt</option>
                <option value="transferred" {{ $filters['settlement_status'] === 'transferred' ? 'selected' : '' }}>Đã chuyển khoản</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Mã đơn</label>
            <input type="text" name="order_code" class="form-control form-control-sm" placeholder="DH-014" value="{{ $filters['order_code'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Tìm kiếm</label>
            <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Mô tả / reference" value="{{ $filters['keyword'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Tiền từ</label>
            <input type="number" step="1000" name="amount_min" class="form-control form-control-sm" value="{{ $filters['amount_min'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Tiền đến</label>
            <input type="number" step="1000" name="amount_max" class="form-control form-control-sm" value="{{ $filters['amount_max'] }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Sắp xếp</label>
            <select name="sort_by" class="form-select form-select-sm">
                <option value="created_at" {{ $filters['sort_by'] === 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                <option value="amount" {{ $filters['sort_by'] === 'amount' ? 'selected' : '' }}>Số tiền</option>
                <option value="settlement_status" {{ $filters['sort_by'] === 'settlement_status' ? 'selected' : '' }}>Trạng thái</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Thứ tự</label>
            <select name="sort_dir" class="form-select form-select-sm">
                <option value="desc" {{ $filters['sort_dir'] === 'desc' ? 'selected' : '' }}>Mới nhất / Lớn nhất</option>
                <option value="asc" {{ $filters['sort_dir'] === 'asc' ? 'selected' : '' }}>Cũ nhất / Nhỏ nhất</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-700">Số dòng / trang</label>
            <select name="per_page" class="form-select form-select-sm">
                @foreach([10,20,50,100] as $pp)
                    <option value="{{ $pp }}" {{ (int) $filters['per_page'] === $pp ? 'selected' : '' }}>{{ $pp }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary btn-sm w-100">
                <i class="bi bi-funnel-fill me-1"></i>Lọc
            </button>
        </div>
    </form>

    <div class="row g-3 mt-2">
        <div class="col-md-3">
            <div class="rounded-3 border p-3 h-100">
                <div class="small text-muted">Tổng thu</div>
                <div class="fs-5 fw-800 text-success">+{{ number_format((float) ($summary->total_in ?? 0), 0, ',', '.') }}đ</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="rounded-3 border p-3 h-100">
                <div class="small text-muted">Tổng phí</div>
                <div class="fs-5 fw-800 text-danger">-{{ number_format((float) ($summary->total_fee ?? 0), 0, ',', '.') }}đ</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="rounded-3 border p-3 h-100">
                <div class="small text-muted">Thực nhận ròng</div>
                <div class="fs-5 fw-800 {{ (float) ($summary->net_amount ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ (float) ($summary->net_amount ?? 0) >= 0 ? '+' : '-' }}{{ number_format(abs((float) ($summary->net_amount ?? 0)), 0, ',', '.') }}đ
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="rounded-3 border p-3 h-100">
                <div class="small text-muted">Đơn đã/chưa đối soát</div>
                <div class="fs-6 fw-800">
                    <span class="text-success">{{ number_format((float) ($orderReconciliation->reconciled_orders ?? 0), 0, ',', '.') }}</span>
                    /
                    <span class="text-warning">{{ number_format((float) ($orderReconciliation->pending_orders ?? 0), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Transaction history --}}
<div class="bg-white rounded-4 shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-bottom fw-700">
        <i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử giao dịch
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ngày</th>
                    <th>Mã đơn</th>
                    <th>Mô tả</th>
                    <th>Loại</th>
                    <th>Đối soát</th>
                    <th class="text-end">Số tiền</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td class="text-muted small">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($tx->donHang)
                            <a href="{{ route('shop.don_hang.show', $tx->donHang->id) }}" class="fw-700 text-decoration-none">
                                {{ $tx->donHang->ma_don }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>{{ $tx->description }}</td>
                    <td>
                        @if($tx->type === 'credit')
                            <span class="badge bg-success-subtle text-success">+ Cộng</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">- Trừ</span>
                        @endif
                    </td>
                    <td>
                        @if(($tx->settlement_status ?? 'pending') === 'transferred')
                            <span class="badge bg-success">Đã chuyển khoản</span>
                            <div class="small text-muted mt-1">
                                {{ optional($tx->transferred_at)->format('d/m H:i') ?? '—' }}
                                · {{ $tx->transferredByUser?->name ?? 'Admin' }}
                            </div>
                        @elseif(($tx->settlement_status ?? 'pending') === 'closed')
                            <span class="badge bg-primary">Đã chốt</span>
                            <div class="small text-muted mt-1">
                                {{ optional($tx->settled_at)->format('d/m H:i') ?? '—' }}
                                · {{ $tx->settledByUser?->name ?? 'Admin' }}
                            </div>
                        @else
                            <span class="badge bg-warning text-dark">Chưa đối soát</span>
                        @endif
                    </td>
                    <td class="text-end fw-700 {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                        {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', '.') }}đ
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>Chưa có giao dịch nào.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasPages())
    <div class="px-4 py-3 border-top">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
