{{--
    Partial dùng chung: Hiển thị chi tiết breakdown phí giao hàng
    Props:
      $donHang  — model DonHang (có shipping_fee, cod_amount, cod_fee, weight, delivery_type)
      $fee      — mảng từ DeliveryFeeService::getBreakdown()
      $role     — 'admin' | 'shop' | 'driver'  (kiểm soát cột nào hiển thị)
--}}
@php
    $shippingFee    = (float) ($donHang->shipping_fee ?? 0);
    $codAmount      = (float) ($donHang->cod_amount   ?? 0);
    $codFee         = (float) ($donHang->cod_fee      ?? 0);
    $totalFee       = $shippingFee + $codFee;
    $shopPayout     = $codAmount - $totalFee;   // Số tiền Shop thực nhận
    $driverIncome   = (float) ($fee['driver_income']      ?? 0);
    $driverTax      = (float) ($fee['driver_tax']         ?? 0);
    $driverReal     = (float) ($fee['driver_real_income'] ?? 0);
    $platformFee    = (float) ($fee['platform_fee']       ?? 0);
    $feeConfig      = \App\Models\SystemFee::current();
@endphp

<div class="fee-breakdown-card">

    {{-- ── Hàng trên: phí vận chuyển ── --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-4">
            <div class="info-label">Phí vận chuyển</div>
            <div class="fee-value text-success">{{ number_format($shippingFee) }}đ</div>
            <small class="text-muted">
                KL tính cước: {{ number_format($donHang->chargeable_weight ?? $donHang->weight ?? 0) }}g
                @if($donHang->delivery_type)
                    &nbsp;|&nbsp; {{ ['standard'=>'Tiêu chuẩn','fast'=>'Nhanh','urgent'=>'Hoả tốc'][$donHang->delivery_type] ?? $donHang->delivery_type }}
                @endif
            </small>
        </div>

        <div class="col-sm-4">
            <div class="info-label">Tiền thu hộ (COD)</div>
            <div class="fee-value {{ $codAmount > 0 ? 'text-primary' : 'text-muted' }}">
                {{ number_format($codAmount) }}đ
            </div>
        </div>

        <div class="col-sm-4">
            <div class="info-label">Phí thu hộ COD ({{ number_format($feeConfig->cod_fee_percent * 100, 1) }}%)</div>
            <div class="fee-value text-danger">{{ number_format($codFee) }}đ</div>
        </div>
    </div>

    <hr class="my-3">

    {{-- ── Tổng phí & phân bổ theo role ── --}}
    <div class="row g-3">

        {{-- Tổng phí Shop phải trả — Shop & Admin đều thấy --}}
        @if($role !== 'driver')
        <div class="col-sm-4">
            <div class="info-label">Tổng phí Shop phải trả</div>
            <div class="fee-value" style="color:#ef4444; font-size:1.1rem;">
                {{ number_format($totalFee) }}đ
            </div>
            <small class="text-muted">(Phí ship + Phí COD)</small>
        </div>

        @if($codAmount > 0)
        <div class="col-sm-4">
            <div class="info-label">Shop thực nhận</div>
            <div class="fee-value" style="color:#16a34a; font-size:1.1rem;">
                {{ number_format(max(0, $shopPayout)) }}đ
            </div>
            <small class="text-muted">(COD − Phí ship − Phí COD)</small>
        </div>
        @endif
        @endif

        {{-- Phân bổ doanh thu — chỉ Admin thấy --}}
        @if($role === 'admin')
        <div class="col-sm-4">
            <div class="info-label">
                Phí nền tảng ({{ number_format($feeConfig->platform_ratio * 100, 0) }}%)
            </div>
            <div class="fee-value">{{ number_format($platformFee) }}đ</div>
        </div>
        @endif

        {{-- Thu nhập tài xế — Admin & Driver đều thấy --}}
        @if($role !== 'shop')
        <div class="col-sm-4">
            <div class="info-label">
                Thu nhập tài xế ({{ number_format($feeConfig->driver_ratio * 100, 0) }}%)
            </div>
            <div class="fee-value" style="color:#0ea5e9;">
                {{ number_format($driverReal) }}đ
                <span class="text-muted" style="font-size:.8rem; font-weight:400;">
                    (sau thuế {{ number_format($feeConfig->driver_tax_percent * 100, 1) }}%:
                    −{{ number_format($driverTax) }}đ)
                </span>
            </div>
        </div>
        @endif

    </div>
</div>

<style>
    .fee-breakdown-card { padding: 4px 0; }
    .fee-value { font-size: .98rem; font-weight: 700; margin-top: 4px; }
    .info-label {
        font-size: .75rem; color: #94a3b8;
        font-weight: 600; text-transform: uppercase; letter-spacing: .04em;
    }
</style>
