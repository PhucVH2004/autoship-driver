@extends('layouts.driver')

@section('page_title', 'Ví của tôi')

@section('content')

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title mb-1">
            <span class="title-icon" style="background: linear-gradient(135deg, #22d3a0, #0EA5E9);">
                <i class="bi bi-wallet2"></i>
            </span>
            Ví của tôi
        </h1>
        <p class="page-subtitle mb-0">
            Xem số dư đối soát và lịch sử giao dịch.
        </p>
    </div>
    <a href="{{ route('driver.dashboard') }}" class="btn btn-light" style="border-radius:12px;">
        <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="driver-card h-100">
            <div class="driver-card-header">
                <i class="bi bi-wallet2" style="color:#22d3a0;"></i>
                Số dư đối soát
            </div>
            <div class="driver-card-body">
                <div style="font-size:.78rem;color:#A0AEC0;font-weight:700;text-transform:uppercase;letter-spacing:.6px;">
                    Trạng thái hiện tại
                </div>
                <div style="font-size:2rem;font-weight:900;color:#1A202C;margin-top:.25rem;">
                    {{ number_format(abs($wallet->balance ?? 0), 0, ',', '.') }} đ
                </div>
                <div class="mt-2">
                    <span class="badge {{ ($wallet->balance < 0) ? 'bg-danger' : (($wallet->balance > 0) ? 'bg-success' : 'bg-primary') }}">
                        {{ $walletStatusLabel }}
                    </span>
                </div>
                <div class="mt-3" style="font-size:.9rem;color:#4A5568;line-height:1.5;">
                    {{ $walletStatusHelp }}
                </div>
                <div class="mt-3" style="font-size:.82rem;color:#718096;">
                    Cập nhật lúc {{ now()->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-3">
            <div class="col-md-6 col-xl-4">
                <div class="driver-card h-100">
                    <div class="driver-card-body">
                        <div class="summary-label">COD đã thu</div>
                        <div class="summary-value text-warning">{{ number_format($codCollectedTotal ?? 0, 0, ',', '.') }} đ</div>
                        <div class="summary-help">Tổng tiền thu hộ từ các đơn COD.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="driver-card h-100">
                    <div class="driver-card-body">
                        <div class="summary-label">Đã đối soát / đã nộp</div>
                        <div class="summary-value text-info">{{ number_format($reconciledTotal ?? 0, 0, ',', '.') }} đ</div>
                        <div class="summary-help">Phần COD đã được hệ thống ghi nhận đối soát.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="driver-card h-100">
                    <div class="driver-card-body">
                        <div class="summary-label">Còn phải nộp</div>
                        <div class="summary-value text-danger">{{ number_format($outstandingAmount ?? 0, 0, ',', '.') }} đ</div>
                        <div class="summary-help">Khoản chênh lệch cần nộp lại nếu ví đang âm.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="driver-card h-100">
                    <div class="driver-card-body">
                        <div class="summary-label">Đang dư</div>
                        <div class="summary-value text-success">{{ number_format($surplusAmount ?? 0, 0, ',', '.') }} đ</div>
                        <div class="summary-help">Số dư hiện có sau đối soát hoặc thưởng.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="driver-card h-100">
                    <div class="driver-card-body">
                        <div class="summary-label">Thưởng KPI</div>
                        <div class="summary-value" style="color:#805AD5;">{{ number_format($kpiRewardTotal ?? 0, 0, ',', '.') }} đ</div>
                        <div class="summary-help">Tổng thưởng KPI đã cộng vào ví.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="driver-card h-100">
                    <div class="driver-card-body">
                        <div class="summary-label">Thu nhập giao hàng</div>
                        <div class="summary-value text-primary">{{ number_format($deliveryIncomeTotal ?? 0, 0, ',', '.') }} đ</div>
                        <div class="summary-help">Tổng thu nhập thực nhận từ đơn giao thành công.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="driver-card">
    <div class="driver-card-header">
        <i class="bi bi-clock-history" style="color:#4F8EF7;"></i>
        Lịch sử giao dịch
    </div>
    <div class="driver-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:.9rem;">
                <thead style="background:#F7FAFC;">
                    <tr>
                        <th class="ps-3">Thời gian</th>
                        <th>Loại giao dịch</th>
                        <th>Mã đơn</th>
                        <th>Diễn giải</th>
                        <th class="text-end pe-3">Số tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $toneStyles = [
                                'success' => 'background:rgba(34,211,160,.12);color:#0C9B73;border:1px solid rgba(34,211,160,.25);',
                                'danger' => 'background:rgba(245,101,101,.12);color:#C53030;border:1px solid rgba(245,101,101,.25);',
                                'info' => 'background:rgba(79,142,247,.12);color:#2563EB;border:1px solid rgba(79,142,247,.25);',
                            ];
                        @endphp
                        <tr>
                            <td class="ps-3" style="color:#718096; white-space:nowrap;">
                                {{ $tx->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td>
                                <span class="badge" style="{{ $toneStyles[$tx->display_tone] ?? $toneStyles['info'] }}">
                                    {{ $tx->display_label }}
                                </span>
                            </td>
                            <td style="color:#4A5568;font-weight:600;white-space:nowrap;">
                                {{ $tx->donHang->ma_don ?? '—' }}
                            </td>
                            <td style="color:#2D3748;font-weight:600;">
                                {{ $tx->description ?? '—' }}
                            </td>
                            <td class="text-end pe-3" style="font-weight:800;color:{{ $tx->type === 'credit' ? '#22d3a0' : '#F56565' }};white-space:nowrap;">
                                {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount ?? 0, 0, ',', '.') }} đ
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Chưa có giao dịch nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-3">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<style>
.summary-label {
    font-size: .78rem;
    color: #A0AEC0;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.summary-value {
    margin-top: .45rem;
    font-size: 1.4rem;
    font-weight: 800;
    line-height: 1.2;
}

.summary-help {
    margin-top: .45rem;
    font-size: .82rem;
    color: #718096;
    line-height: 1.45;
}
</style>
@endpush
