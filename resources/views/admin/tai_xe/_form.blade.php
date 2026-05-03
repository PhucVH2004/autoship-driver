{{-- admin/tai_xe/_form.blade.php — khớp cột DB thực tế --}}
@php $model = $model ?? null; @endphp

@if($errors->any())
<div class="alert alert-danger rounded-3 mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-600">Họ tên <span class="text-danger">*</span></label>
        <input type="text" name="ho_ten" class="form-control @error('ho_ten') is-invalid @enderror"
               value="{{ old('ho_ten', $model?->ho_ten) }}" placeholder="Trần Văn A" required>
        @error('ho_ten')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Số điện thoại</label>
        <input type="text" name="so_dien_thoai" class="form-control @error('so_dien_thoai') is-invalid @enderror"
               value="{{ old('so_dien_thoai', $model?->so_dien_thoai) }}" placeholder="0901 234 567">
        @error('so_dien_thoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Biển số xe</label>
        <input type="text" name="bien_so_xe" class="form-control @error('bien_so_xe') is-invalid @enderror"
               value="{{ old('bien_so_xe', $model?->bien_so_xe) }}" placeholder="51F-12345">
        @error('bien_so_xe')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-600">Trạng thái</label>
        <select name="trang_thai" class="form-select @error('trang_thai') is-invalid @enderror">
            <option value="">— Chọn trạng thái —</option>
            <option value="Ranh"      {{ old('trang_thai', $model?->trang_thai) == 'Ranh'      ? 'selected' : '' }}>Rảnh</option>
            <option value="Dang giao" {{ old('trang_thai', $model?->trang_thai) == 'Dang giao' ? 'selected' : '' }}>Đang giao</option>
            <option value="Tam nghi"  {{ old('trang_thai', $model?->trang_thai) == 'Tam nghi'  ? 'selected' : '' }}>Tạm nghỉ</option>
        </select>
        @error('trang_thai')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if(!$model)
    <div class="col-md-6">
        <label class="form-label fw-600">Mật khẩu đăng nhập <span class="text-danger">*</span></label>
        <input type="password" name="mat_khau" class="form-control @error('mat_khau') is-invalid @enderror"
               placeholder="Nhập mật khẩu cho tài xế" required minlength="6">
        @error('mat_khau')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    @endif
</div>
