@extends('layouts.shop')
@section('page_title', 'Tạo đơn hàng mới')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="fw-800 fs-4 mb-1">
            <i class="bi bi-plus-circle" style="color:#6C63FF;"></i> Tạo đơn hàng mới
        </h1>
        <p class="text-muted mb-0">Hệ thống sẽ tự tính phí ship dựa theo cân nặng và kích thước.</p>
    </div>
    <a href="{{ route('shop.don_hang.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Quay lại
    </a>
</div>

<form action="{{ route('shop.don_hang.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        {{-- Thông tin người nhận --}}
        <div class="col-lg-5">
            <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                <h6 class="fw-700 mb-3">
                    <i class="bi bi-person-fill me-2 text-primary"></i>Thông tin người nhận
                </h6>
                <div class="mb-3">
                    <label class="form-label fw-600">Tên người nhận <span class="text-danger">*</span></label>
                    <input type="text" name="ten_nguoi_nhan" class="form-control"
                           value="{{ old('ten_nguoi_nhan') }}" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" name="sdt_nguoi_nhan" class="form-control"
                           value="{{ old('sdt_nguoi_nhan') }}" placeholder="0900 000 000" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600 d-block mb-2">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                    @include('components.address-picker', [
                        'prefix' => 'newkh',
                        'required' => true,
                        'diaChiCuThe' => old('newkh_dia_chi_cu_the'),
                        'latField' => 'latitude',
                        'lngField' => 'longitude',
                    ])
                </div>
                <div>
                    <label class="form-label fw-600">Ghi chú cho tài xế</label>
                    <textarea name="ghi_chu" class="form-control" rows="2"
                              placeholder="Gọi trước khi giao, để trước cổng...">{{ old('ghi_chu') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Thông số bưu kiện + COD --}}
        <div class="col-lg-4">
            <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                <h6 class="fw-700 mb-3">
                    <i class="bi bi-box me-2 text-warning"></i>Thông số bưu kiện
                </h6>
                <div class="mb-3">
                    <label class="form-label fw-600">Loại hình giao hàng</label>
                    <select name="delivery_type" id="delivery_type" class="form-select" onchange="estimateFee()">
                        <option value="standard" {{ old('delivery_type','standard') === 'standard' ? 'selected' : '' }}>Tiêu chuẩn</option>
                        <option value="fast"     {{ old('delivery_type') === 'fast'     ? 'selected' : '' }}>Giao nhanh</option>
                        <option value="urgent"   {{ old('delivery_type') === 'urgent'   ? 'selected' : '' }}>Hoả tốc</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-600">Cân nặng (gram)</label>
                    <input type="number" name="weight" id="weight" class="form-control"
                           value="{{ old('weight', 500) }}" min="0" step="50" oninput="estimateFee()">
                    <div class="form-text">Ví dụ: 500 = 500 gram</div>
                </div>
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label fw-600">Dài (cm)</label>
                        <input type="number" name="length" id="length" class="form-control"
                               value="{{ old('length', 20) }}" min="0" oninput="estimateFee()">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-600">Rộng (cm)</label>
                        <input type="number" name="width" id="width" class="form-control"
                               value="{{ old('width', 15) }}" min="0" oninput="estimateFee()">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-600">Cao (cm)</label>
                        <input type="number" name="height" id="height" class="form-control"
                               value="{{ old('height', 10) }}" min="0" oninput="estimateFee()">
                    </div>
                </div>
                <div class="form-text mt-1">
                    <i class="bi bi-info-circle text-primary"></i>
                    Phí = Max(cân thực, L×R×C/6 kg) × đơn giá bậc thang.
                </div>
            </div>

            <div class="bg-white rounded-4 shadow-sm p-4">
                <h6 class="fw-700 mb-3">
                    <i class="bi bi-cash-coin me-2 text-success"></i>Tiền thu hộ (COD)
                </h6>
                <div>
                    <label class="form-label fw-600">Số tiền COD (VNĐ)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                        <input type="number" name="cod_amount" id="cod_amount" class="form-control"
                               value="{{ old('cod_amount', 0) }}" min="0" step="1000" placeholder="0"
                               oninput="estimateFee()">
                        <span class="input-group-text">đ</span>
                    </div>
                    <div class="form-text">
                        Phí COD và phí vận chuyển sẽ được trừ khi đối soát.
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview phí (realtime) --}}
        <div class="col-lg-3">
            <div class="bg-white rounded-4 shadow-sm p-4 sticky-top" style="top: 80px;">
                <h6 class="fw-700 mb-3">
                    <i class="bi bi-calculator me-2 text-info"></i>Dự tính phí
                </h6>
                <div id="fee-preview">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <small class="text-muted">KL tính cước:</small>
                        <small id="prev-weight">—</small>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Phí vận chuyển:</span>
                        <strong id="prev-shipping">—</strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom" id="row-cod-fee" style="display:none!important">
                        <span>Phí thu hộ COD:</span>
                        <span id="prev-cod-fee" class="text-danger">—</span>
                    </div>
                    <div class="d-flex justify-content-between py-3 border-top">
                        <strong>Tổng phí phải trả:</strong>
                        <strong id="prev-total" class="text-primary">—</strong>
                    </div>
                    <div id="row-shop-receive" class="alert alert-success py-2 mt-2" style="display:none">
                        <small>
                            <i class="bi bi-wallet2 me-1"></i>
                            <strong>Shop thực nhận: <span id="prev-shop-receive">0</span>đ</strong>
                            <br><em class="text-muted">(COD - Phí ship - Phí COD)</em>
                        </small>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <small class="text-muted"><i class="bi bi-arrow-repeat me-1"></i>Tự động cập nhật</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-3">
        <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-send-fill me-2"></i>Tạo đơn hàng
        </button>
        <a href="{{ route('shop.don_hang.index') }}" class="btn btn-outline-secondary">Hủy</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Lấy cấu hình phí từ server (truyền qua Blade)
    const feeConfig = {
        standard: {{ \App\Models\SystemFee::current()->standard_delivery_fee }},
        fast:     {{ \App\Models\SystemFee::current()->fast_delivery_fee }},
        urgent:   {{ \App\Models\SystemFee::current()->urgent_delivery_fee }},
        baseWeight: {{ \App\Models\SystemFee::current()->base_weight }},
        stepWeight: {{ \App\Models\SystemFee::current()->step_weight }},
        stepPrice:  {{ \App\Models\SystemFee::current()->step_price }},
        zoneMultiplier: {{ \App\Models\SystemFee::current()->zone_multiplier }},
        codFeePercent: {{ \App\Models\SystemFee::current()->cod_fee_percent }},
    };

    function fmtVnd(num) {
        return Math.round(num).toLocaleString('vi-VN') + 'đ';
    }

    function estimateFee() {
        const weight = parseInt(document.getElementById('weight').value) || 0;
        const length = parseInt(document.getElementById('length').value) || 0;
        const width  = parseInt(document.getElementById('width').value)  || 0;
        const height = parseInt(document.getElementById('height').value) || 0;
        const codAmount = parseFloat(document.getElementById('cod_amount').value) || 0;
        const deliveryType = document.getElementById('delivery_type').value;

        // Trọng lượng quy đổi (gram)
        const volumetric = (length * width * height) / 6000 * 1000;
        const chargeableWeight = Math.max(weight, volumetric);

        // Phí giao hàng theo loại hình (base price)
        const basePrice = feeConfig[deliveryType] || feeConfig.standard;

        // Tính phí bậc thang
        let shippingFee = basePrice;
        if (chargeableWeight > feeConfig.baseWeight) {
            const excess = chargeableWeight - feeConfig.baseWeight;
            const steps  = Math.ceil(excess / feeConfig.stepWeight);
            shippingFee += steps * feeConfig.stepPrice;
        }
        shippingFee = shippingFee * feeConfig.zoneMultiplier;

        // Phí COD
        const codFee = codAmount * feeConfig.codFeePercent;
        const totalFee = shippingFee + codFee;
        const shopReceive = codAmount - totalFee;

        // Cập nhật UI
        document.getElementById('prev-weight').textContent = chargeableWeight.toLocaleString('vi-VN') + 'g';
        document.getElementById('prev-shipping').textContent = fmtVnd(shippingFee);
        document.getElementById('prev-total').textContent = fmtVnd(totalFee);

        const rowCodFee = document.getElementById('row-cod-fee');
        const rowShopReceive = document.getElementById('row-shop-receive');

        if (codAmount > 0) {
            document.getElementById('prev-cod-fee').textContent = fmtVnd(codFee);
            rowCodFee.style.setProperty('display', 'flex', 'important');
            document.getElementById('prev-shop-receive').textContent = fmtVnd(Math.max(0, shopReceive));
            rowShopReceive.style.display = 'block';
        } else {
            rowCodFee.style.setProperty('display', 'none', 'important');
            rowShopReceive.style.display = 'none';
        }
    }

    // Chạy ngay khi load trang
    document.addEventListener('DOMContentLoaded', estimateFee);
</script>
@endpush
