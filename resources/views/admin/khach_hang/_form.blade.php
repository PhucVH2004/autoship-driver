{{-- admin/khach_hang/_form.blade.php --}}
@php $model = $model ?? null; @endphp

@if($errors->any())
<div class="alert alert-danger rounded-3 mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label fw-600">Tên khách hàng <span class="text-danger">*</span></label>
        <input type="text" name="ten_khach"
               class="form-control @error('ten_khach') is-invalid @enderror"
               value="{{ old('ten_khach', $model?->ten_khach) }}"
               placeholder="Nguyễn Văn A" required>
        @error('ten_khach')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Số điện thoại <span class="text-danger">*</span></label>
        <input type="text" name="so_dien_thoai"
               class="form-control @error('so_dien_thoai') is-invalid @enderror"
               value="{{ old('so_dien_thoai', $model?->so_dien_thoai) }}"
               placeholder="0901 234 567" required>
        @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ── Địa chỉ theo cấp hành chính ──────────────────────────────── --}}
<div class="card border-0 rounded-3 mb-3" style="background:#F8FAFC;">
    <div class="card-body pb-2">
        <div class="fw-600 mb-3" style="font-size:.85rem;color:#4A5568;">
            <i class="bi bi-geo-alt me-1 text-primary"></i> Địa chỉ giao hàng
            <span class="text-muted fw-400">(Chọn Tỉnh → Quận → Xã → Số nhà)</span>
        </div>

        @include('components.address-picker', [
            'prefix'      => 'kh',
            'tinhId'      => old('kh_tinh_id', $model?->tinh_thanh_id),
            'quanId'      => old('kh_quan_id', $model?->quan_huyen_id),
            'xaId'        => old('kh_xa_id',   $model?->xa_phuong_id),
            'diaChiCuThe' => old('kh_dia_chi_cu_the', $model?->dia_chi_cu_the),
            'latField'    => 'latitude',
            'lngField'    => 'longitude',
            'required'    => false,
        ])
    </div>
</div>

