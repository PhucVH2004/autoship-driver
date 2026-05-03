@extends('layouts.admin')
@section('page_title', 'Cấu hình phí hệ thống')

@section('content')
<div class="mb-4">
    <h1 class="page-heading">Cấu hình phí hệ thống</h1>
    <p class="page-subtext">Thay đổi các mức phí và tỷ lệ chia sẻ áp dụng cho toàn hệ thống.</p>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 shadow-sm" style="max-width:860px;">
    <div class="card-body p-4">
        <form action="{{ route('admin.system_fees.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ══════════════════════════════════════════════════════════
                 SECTION 1 — Phí giao hàng cơ bản (theo loại hình giao)
                 Dùng bởi: DeliveryFeeService::calculateDeliveryFee()
            ══════════════════════════════════════════════════════════ --}}
            <h5 class="mb-3 text-primary"><i class="bi bi-truck me-2"></i>Phí giao hàng cơ bản (theo loại hình)</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Giao tiêu chuẩn (VNĐ)</label>
                    <input type="number" name="standard_delivery_fee"
                        class="form-control @error('standard_delivery_fee') is-invalid @enderror"
                        value="{{ old('standard_delivery_fee', $fee->standard_delivery_fee) }}"
                        min="0" required>
                    <div class="form-text">Mặc định: 21.000đ</div>
                    @error('standard_delivery_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Giao nhanh (VNĐ)</label>
                    <input type="number" name="fast_delivery_fee"
                        class="form-control @error('fast_delivery_fee') is-invalid @enderror"
                        value="{{ old('fast_delivery_fee', $fee->fast_delivery_fee) }}"
                        min="0" required>
                    <div class="form-text">Mặc định: 40.000đ</div>
                    @error('fast_delivery_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Giao hoả tốc (VNĐ)</label>
                    <input type="number" name="urgent_delivery_fee"
                        class="form-control @error('urgent_delivery_fee') is-invalid @enderror"
                        value="{{ old('urgent_delivery_fee', $fee->urgent_delivery_fee) }}"
                        min="0" required>
                    <div class="form-text">Mặc định: 60.000đ</div>
                    @error('urgent_delivery_fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <hr class="text-muted mb-4">

            {{-- ══════════════════════════════════════════════════════════
                 SECTION 2 — Định giá theo khối lượng (matrix pricing)
                 Dùng bởi: PricingService::calculateShippingFee()
            ══════════════════════════════════════════════════════════ --}}
            <h5 class="mb-3 text-warning"><i class="bi bi-boxes me-2"></i>Định giá theo khối lượng (bậc thang)</h5>
            <p class="text-muted small mb-3">
                Công thức: <code>phí = base_price + ceil((khối_lượng - base_weight) / step_weight) × step_price</code>
                &nbsp;×&nbsp; <code>zone_multiplier</code>
            </p>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Khối lượng gốc (gram)</label>
                    <input type="number" name="base_weight"
                        class="form-control @error('base_weight') is-invalid @enderror"
                        value="{{ old('base_weight', $fee->base_weight) }}"
                        min="1" required>
                    <div class="form-text">Ngưỡng KL miễn tính thêm. Mặc định: 1.000g</div>
                    @error('base_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cước phí gốc (VNĐ)</label>
                    <input type="number" name="base_price"
                        class="form-control @error('base_price') is-invalid @enderror"
                        value="{{ old('base_price', $fee->base_price) }}"
                        min="0" required>
                    <div class="form-text">Phí áp dụng cho mức KL gốc. Mặc định: 21.000đ</div>
                    @error('base_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Bước khối lượng (gram)</label>
                    <input type="number" name="step_weight"
                        class="form-control @error('step_weight') is-invalid @enderror"
                        value="{{ old('step_weight', $fee->step_weight) }}"
                        min="1" required>
                    <div class="form-text">Mỗi bước vượt ngưỡng. Mặc định: 500g</div>
                    @error('step_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cước thêm / bước (VNĐ)</label>
                    <input type="number" name="step_price"
                        class="form-control @error('step_price') is-invalid @enderror"
                        value="{{ old('step_price', $fee->step_price) }}"
                        min="0" required>
                    <div class="form-text">Phí cộng thêm cho mỗi bước. Mặc định: 5.000đ</div>
                    @error('step_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Hệ số vùng (zone_multiplier)</label>
                    <input type="number" step="0.01" name="zone_multiplier"
                        class="form-control @error('zone_multiplier') is-invalid @enderror"
                        value="{{ old('zone_multiplier', $fee->zone_multiplier) }}"
                        min="0.1" max="10" required>
                    <div class="form-text">1.0 = không nhân thêm; 1.5 = tuyến xa +50%. Mặc định: 1.0</div>
                    @error('zone_multiplier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <hr class="text-muted mb-4">

            {{-- ══════════════════════════════════════════════════════════
                 SECTION 3 — Tỷ lệ chia doanh thu & Thuế & COD
                 Dùng bởi: DeliveryFeeService::calculateDriverIncome/PlatformFee/Tax/CodFee()
            ══════════════════════════════════════════════════════════ --}}
            <h5 class="mb-3 text-success"><i class="bi bi-pie-chart me-2"></i>Tỷ lệ chia doanh thu</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tỷ lệ tài xế được hưởng (0 – 1)</label>
                    <div class="input-group">
                        <input type="number" step="0.001" name="driver_ratio"
                            class="form-control @error('driver_ratio') is-invalid @enderror"
                            value="{{ old('driver_ratio', $fee->driver_ratio) }}"
                            min="0" max="1" required>
                        <span class="input-group-text">× 100%</span>
                        @error('driver_ratio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Ví dụ: 0.75 → tài xế nhận 75% phí ship. Mặc định: 0.75</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tỷ lệ nền tảng giữ lại (0 – 1)</label>
                    <div class="input-group">
                        <input type="number" step="0.001" name="platform_ratio"
                            class="form-control @error('platform_ratio') is-invalid @enderror"
                            value="{{ old('platform_ratio', $fee->platform_ratio) }}"
                            min="0" max="1" required>
                        <span class="input-group-text">× 100%</span>
                        @error('platform_ratio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Ví dụ: 0.25 → nền tảng giữ 25%. Mặc định: 0.25</div>
                </div>
            </div>

            <h5 class="mb-3 text-danger"><i class="bi bi-receipt me-2"></i>Thuế & Phí thu hộ COD</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Thuế thu nhập tài xế (0 – 1)</label>
                    <div class="input-group">
                        <input type="number" step="0.001" name="driver_tax_percent"
                            class="form-control @error('driver_tax_percent') is-invalid @enderror"
                            value="{{ old('driver_tax_percent', $fee->driver_tax_percent) }}"
                            min="0" max="1" required>
                        <span class="input-group-text">× 100%</span>
                        @error('driver_tax_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Ví dụ: 0.045 → khấu trừ 4.5% thu nhập tài xế. Mặc định: 0.045</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phí thu hộ COD (0 – 1)</label>
                    <div class="input-group">
                        <input type="number" step="0.001" name="cod_fee_percent"
                            class="form-control @error('cod_fee_percent') is-invalid @enderror"
                            value="{{ old('cod_fee_percent', $fee->cod_fee_percent) }}"
                            min="0" max="1" required>
                        <span class="input-group-text">× 100%</span>
                        @error('cod_fee_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-text">Ví dụ: 0.01 → thu 1% giá trị COD làm phí thu hộ. Mặc định: 0.01</div>
                </div>
            </div>

            <hr class="text-muted mb-4">

            {{-- ══════════════════════════════════════════════════════════
                 SECTION 4 — Thưởng KPI tài xế
                 Dùng bởi: FinancialService::recordDriverTransaction()
                            (đọc từ DriverKpiConfig::current())
            ══════════════════════════════════════════════════════════ --}}
            <h5 class="mb-3 text-info"><i class="bi bi-trophy me-2"></i>Thưởng KPI tài xế</h5>
            <p class="text-muted small mb-3">
                Số tiền thưởng cộng thêm vào ví tài xế sau mỗi lần thực hiện thành công các tác vụ.
            </p>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Thưởng lấy hàng (VNĐ)</label>
                    <input type="number" name="pickup_reward"
                        class="form-control @error('pickup_reward') is-invalid @enderror"
                        value="{{ old('pickup_reward', $kpi->pickup_reward) }}"
                        min="0" required>
                    <div class="form-text">Thưởng khi tài xế lấy hàng thành công. Mặc định: 5.000đ</div>
                    @error('pickup_reward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Thưởng giao hàng (VNĐ)</label>
                    <input type="number" name="delivery_reward"
                        class="form-control @error('delivery_reward') is-invalid @enderror"
                        value="{{ old('delivery_reward', $kpi->delivery_reward) }}"
                        min="0" required>
                    <div class="form-text">Thưởng khi tài xế giao hàng thành công. Mặc định: 10.000đ</div>
                    @error('delivery_reward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Thưởng hoàn hàng (VNĐ)</label>
                    <input type="number" name="return_reward"
                        class="form-control @error('return_reward') is-invalid @enderror"
                        value="{{ old('return_reward', $kpi->return_reward) }}"
                        min="0" required>
                    <div class="form-text">Thưởng khi tài xế hoàn hàng thành công. Mặc định: 3.000đ</div>
                    @error('return_reward') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-save me-1"></i> Lưu cấu hình
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
