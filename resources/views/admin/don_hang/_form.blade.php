{{-- admin/don_hang/_form.blade.php --}}
@php $model = $model ?? null; @endphp

@if($errors->any())
<div class="alert alert-danger rounded-3 mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-3">
    {{-- Shop/Người gửi (sender_id) --}}
    <div class="col-md-12 mb-2">
        <label class="form-label fw-600 text-primary"><i class="bi bi-shop me-1"></i> Shop / Người gửi <span class="text-danger">*</span></label>
        <select name="sender_id" class="form-select @error('sender_id') is-invalid @enderror" required>
            <option value="">— Chọn shop gửi hàng —</option>
            @foreach($shops as $shop)
                <option value="{{ $shop->id }}"
                    {{ old('sender_id', $model?->sender_id) == $shop->id ? 'selected' : '' }}>
                    {{ $shop->ten_shop }} ({{ $shop->so_dien_thoai }})
                </option>
            @endforeach
        </select>
        @error('sender_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Khách hàng (Người nhận) - Có 2 Tab: Chọn cũ / Tạo mới --}}
    <div class="col-md-12 mb-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                <h6 class="mb-0 text-success fw-600"><i class="bi bi-person-bounding-box me-1"></i> Người nhận (Khách hàng) <span class="text-danger">*</span></h6>
                <ul class="nav nav-tabs mt-3 border-bottom-0" id="khachHangTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-500" id="old-kh-tab" data-bs-toggle="tab" data-bs-target="#old-kh" type="button" role="tab" onclick="document.getElementById('is_new_customer').value='0'">
                            <i class="bi bi-search me-1"></i>Chọn khách cũ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-500 text-primary" id="new-kh-tab" data-bs-toggle="tab" data-bs-target="#new-kh" type="button" role="tab" onclick="document.getElementById('is_new_customer').value='1'">
                            <i class="bi bi-plus-circle me-1"></i>Nhập khách mới
                        </button>
                    </li>
                </ul>
                <input type="hidden" name="is_new_customer" id="is_new_customer" value="{{ old('is_new_customer', '0') }}">
            </div>
            <div class="card-body bg-light rounded-bottom">
                <div class="tab-content" id="khachHangTabsContent">
                    {{-- Tab 1: Khách cũ --}}
                    <div class="tab-pane fade show active" id="old-kh" role="tabpanel">
                        <select name="khach_hang_id" id="khach_hang_id" class="form-select @error('khach_hang_id') is-invalid @enderror">
                            <option value="">— Tìm & chọn khách hàng đã có —</option>
                            @foreach($khachHangs as $kh)
                                <option value="{{ $kh->id }}"
                                    {{ old('khach_hang_id', $model?->khach_hang_id) == $kh->id ? 'selected' : '' }}>
                                    {{ $kh->so_dien_thoai }} - {{ $kh->ten_khach }} - {{ $kh->dia_chi }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Tìm theo SĐT hoặc tên khách hàng.</div>
                        @error('khach_hang_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tab 2: Khách mới --}}
                    <div class="tab-pane fade" id="new-kh" role="tabpanel">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Tên khách hàng</label>
                                <input type="text" name="ten_khach" class="form-control" value="{{ old('ten_khach') }}" placeholder="VD: Nguyễn Văn A">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Số điện thoại</label>
                                <input type="text" name="so_dien_thoai" class="form-control" value="{{ old('so_dien_thoai') }}" placeholder="VD: 0901234567">
                            </div>
                        </div>
                        @include('components.address-picker', [
                            'prefix'      => 'newkh',
                            'required'    => false,
                            'diaChiCuThe' => old('newkh_dia_chi_cu_the'),
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kích thước & Cân nặng --}}
    <div class="col-md-3">
        <label class="form-label fw-600">Cân nặng (gram)</label>
        <input type="number" name="weight" class="form-control" value="{{ old('weight', $model?->weight ?? 500) }}" min="0">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-600">Dài (cm)</label>
        <input type="number" name="length" class="form-control" value="{{ old('length', $model?->length ?? 10) }}" min="0">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-600">Rộng (cm)</label>
        <input type="number" name="width" class="form-control" value="{{ old('width', $model?->width ?? 10) }}" min="0">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-600">Cao (cm)</label>
        <input type="number" name="height" class="form-control" value="{{ old('height', $model?->height ?? 10) }}" min="0">
    </div>

    {{-- Tài xế --}}
    <div class="col-md-6">
        <label class="form-label fw-600">Tài xế phụ trách</label>
        <select name="tai_xe_id" class="form-select @error('tai_xe_id') is-invalid @enderror">
            <option value="">— Chưa phân công —</option>
            @foreach($taiXes as $tx)
                <option value="{{ $tx->id }}"
                    {{ old('tai_xe_id', $model?->tai_xe_id) == $tx->id ? 'selected' : '' }}>
                    {{ $tx->ho_ten }}
                    @if($tx->bien_so_xe) — {{ $tx->bien_so_xe }} @endif
                </option>
            @endforeach
        </select>
        @error('tai_xe_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Loại giao --}}
    <div class="col-md-6">
        <label class="form-label fw-600">Loại giao hàng</label>
        @php $deliveryType = old('delivery_type', $model?->delivery_type ?? 'standard'); @endphp
        <select name="delivery_type" class="form-select @error('delivery_type') is-invalid @enderror">
            <option value="standard" {{ $deliveryType === 'standard' ? 'selected' : '' }}>Giao tiêu chuẩn</option>
            <option value="fast" {{ $deliveryType === 'fast' ? 'selected' : '' }}>Giao nhanh</option>
            <option value="urgent" {{ $deliveryType === 'urgent' ? 'selected' : '' }}>Giao hỏa tốc</option>
        </select>
        @error('delivery_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if($model)
    {{-- Trạng thái chỉ chỉnh khi sửa đơn --}}
    <div class="col-md-6">
        <label class="form-label fw-600">Trạng thái đơn hàng <span class="text-danger">*</span></label>
        <select name="trang_thai_id" class="form-select @error('trang_thai_id') is-invalid @enderror" required>
            <option value="">— Chọn trạng thái —</option>
            @foreach($trangThais as $tt)
                <option value="{{ $tt->id }}" {{ old('trang_thai_id', $model?->trang_thai_id ?? \App\Models\TrangThaiDonHang::CHO_XU_LY) == $tt->id ? 'selected' : '' }}>
                    {{ $tt->ten_trang_thai }}
                </option>
            @endforeach
        </select>
        @error('trang_thai_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @endif

    {{-- COD --}}
    <div class="col-md-6">
        <label class="form-label fw-600">Số tiền thu hộ COD (VNĐ)</label>
        <input type="number" name="cod_amount" class="form-control @error('cod_amount') is-invalid @enderror"
               value="{{ old('cod_amount', $model?->cod_amount ?? 0) }}" min="0" step="1000" placeholder="0">
        @error('cod_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Thời gian giao dự kiến --}}
    <div class="col-md-6">
        <label class="form-label fw-600">Thời gian giao dự kiến</label>
        <input type="datetime-local" name="thoi_gian_giao_du_kien"
               class="form-control @error('thoi_gian_giao_du_kien') is-invalid @enderror"
               value="{{ old('thoi_gian_giao_du_kien', $model?->thoi_gian_giao_du_kien?->format('Y-m-d\TH:i')) }}">
        @error('thoi_gian_giao_du_kien')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Ghi chú --}}
    <div class="col-12">
        <label class="form-label fw-600">Ghi chú</label>
        <textarea name="ghi_chu" class="form-control @error('ghi_chu') is-invalid @enderror"
                  rows="2" placeholder="Gọi trước khi giao, để trước cổng...">{{ old('ghi_chu', $model?->ghi_chu) }}</textarea>
        @error('ghi_chu')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@push('extra_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Restore tab state on validation fail
    const isNew = document.getElementById('is_new_customer').value;
    if (isNew === '1') {
        const newTabBtn = document.getElementById('new-kh-tab');
        const bsTab = new bootstrap.Tab(newTabBtn);
        bsTab.show();
    }
});
</script>
@endpush
