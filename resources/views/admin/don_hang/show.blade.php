{{-- admin/don_hang/show.blade.php — Chi tiết đơn hàng + cập nhật trạng thái --}}
@extends('layouts.admin')

@section('page_title', 'Chi tiết ' . $donHang->ma_don)

@push('extra_css')
<style>
    .timeline { position: relative; padding-left: 28px; }
    .timeline::before {
        content: ''; position: absolute; left: 10px; top: 0; bottom: 0;
        width: 2px; background: #e2e8f0;
    }
    .timeline-item { position: relative; margin-bottom: 20px; }
    .timeline-dot {
        position: absolute; left: -23px; top: 4px;
        width: 14px; height: 14px; border-radius: 50%;
        background: #3b82f6; border: 2px solid #fff;
        box-shadow: 0 0 0 2px #3b82f6;
    }
    .timeline-dot.dot-latest { background: #16a34a; box-shadow: 0 0 0 2px #16a34a; }
    .info-label { font-size: .78rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
    .info-value { font-size: .95rem; color: #0f172a; font-weight: 500; }
</style>
@endpush

@section('content')

{{-- Flash messages --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('info'))
<div class="alert alert-info alert-dismissible fade show rounded-3 mb-4" role="alert">
    <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Breadcrumb --}}
<div class="mb-4">
    <h1 class="page-heading">Chi tiết đơn hàng</h1>
    <p class="page-subtext">
        <a href="{{ route('admin.don_hang.index') }}" class="text-muted text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách
        </a>
    </p>
</div>

<div class="row g-4">

    {{-- ══ CỘT TRÁI: Thông tin chính + Timeline ══════════════════════════════ --}}
    <div class="col-lg-8">

        {{-- Thông tin đơn hàng --}}
        <div class="data-table-wrapper mb-4">
            <div class="table-header">
                <h5><i class="bi bi-box-seam me-2 text-primary"></i>Thông tin đơn hàng</h5>
                <div class="d-flex gap-2">
                    <span class="status-badge {{ $donHang->trangThai?->css_class ?? '' }}">
                        {{ $donHang->trangThai?->ten_trang_thai ?? '—' }}
                    </span>
                    @if($donHang->trang_thai_id === 3 && $donHang->shop)
                        @if($donHang->da_doi_soat)
                            <span class="badge bg-success-subtle text-success border border-success rounded-2 px-3">
                                <i class="bi bi-check-circle me-1"></i>Đã đối soát
                            </span>
                        @else
                            <form action="{{ route('admin.don_hang.doi_soat', $donHang) }}"
                                  method="POST"
                                  onsubmit="return confirm('Xác nhận đối soát COD cho Shop này?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success rounded-2">
                                    <i class="bi bi-cash-coin me-1"></i>Đối soát COD
                                </button>
                            </form>
                        @endif
                    @endif
                    <a href="{{ route('admin.don_hang.edit', $donHang) }}" class="btn btn-sm btn-outline-warning rounded-2">
                        <i class="bi bi-pencil me-1"></i>Sửa toàn bộ
                    </a>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-4">
                    <div class="col-sm-4">
                        <div class="info-label">Mã đơn hàng</div>
                        <div class="info-value" style="font-family:monospace; color:#1e40af; font-size:1.1rem;">
                            {{ $donHang->ma_don }}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Thời gian tạo</div>
                        <div class="info-value">{{ $donHang->created_at->format('H:i — d/m/Y') }}</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="info-label">Tổng tiền</div>
                        <div class="info-value" style="color:#16a34a; font-weight:700;">
                            {{ $donHang->tong_tien ? number_format($donHang->tong_tien, 0, ',', '.') . ' đ' : '—' }}
                        </div>
                    </div>
                    @if($donHang->thoi_gian_giao_du_kien)
                    <div class="col-sm-4">
                        <div class="info-label">Giao dự kiến</div>
                        <div class="info-value">{{ $donHang->thoi_gian_giao_du_kien->format('H:i — d/m/Y') }}</div>
                    </div>
                    @endif
                    @if($donHang->thoi_gian_hoan_thanh)
                    <div class="col-sm-4">
                        <div class="info-label">Hoàn thành lúc</div>
                        <div class="info-value">{{ $donHang->thoi_gian_hoan_thanh->format('H:i — d/m/Y') }}</div>
                    </div>
                    @endif
                    @if($donHang->ghi_chu)
                    <div class="col-12">
                        <div class="info-label">Ghi chú</div>
                        <div class="info-value">{{ $donHang->ghi_chu }}</div>
                    </div>
                    @endif
                    
                    {{-- Ảnh xác nhận giao hàng (YC7) --}}
                    @if($donHang->delivery_photo)
                    <div class="col-12 mt-4 pt-4 border-top">
                        <div class="info-label text-primary mb-3">
                            <i class="bi bi-camera me-1"></i>Ảnh xác nhận giao hàng
                        </div>
                        <a href="{{ asset('storage/' . $donHang->delivery_photo) }}" target="_blank" class="d-inline-block">
                            <img src="{{ asset('storage/' . $donHang->delivery_photo) }}" 
                                 alt="Ảnh xác nhận giao hàng" 
                                 style="max-width:300px; height:auto; border-radius:8px; border:2px solid #e2e8f0; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); cursor:zoom-in;">
                        </a>
                        <div class="mt-2 text-muted" style="font-size:0.8rem;">
                            <i class="bi bi-info-circle me-1"></i>Click vào ảnh để xem kích thước đầy đủ
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Breakdown phí (logistics-style) --}}
        <div class="data-table-wrapper mb-4">
            <div class="table-header">
                <h5><i class="bi bi-cash-coin me-2 text-success"></i>Chi tiết phí giao hàng</h5>
                <small class="text-muted">Phí theo khối lượng bậc thang + loại hình giao + COD</small>
            </div>
            <div class="p-4">
                @include('components.fee-breakdown', ['donHang' => $donHang, 'fee' => $fee, 'role' => 'admin'])

                @if($donHang->trang_thai_id === 3)
                    <div class="mt-3 text-muted" style="font-size:.82rem;">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        Đơn đã hoàn thành: phí đã được chốt và lưu vào DB.
                    </div>
                @else
                    <div class="mt-3 text-muted" style="font-size:.82rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Đây là phí đã tính tại thời điểm tạo đơn; sẽ được chốt khi đơn hoàn thành.
                    </div>
                @endif
            </div>
        </div>

        {{-- Timeline lịch sử trạng thái --}}
        <div class="data-table-wrapper">
            <div class="table-header">
                <h5><i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử trạng thái</h5>
                <small class="text-muted">Mới nhất ở trên</small>
            </div>
            <div class="p-4">
                @if($donHang->lichSuTrangThais->isEmpty())
                    <p class="text-muted text-center py-3">
                        <i class="bi bi-info-circle me-1"></i>Chưa có lịch sử thay đổi trạng thái.
                    </p>
                @else
                <div class="timeline">
                    @foreach($donHang->lichSuTrangThais as $ls)
                    <div class="timeline-item">
                        {{-- Dot xanh lá cho bản ghi mới nhất --}}
                        <div class="timeline-dot {{ $loop->first ? 'dot-latest' : '' }}"></div>
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <span class="status-badge {{ $ls->trangThai?->css_class ?? '' }}">
                                    {{ $ls->trangThai?->ten_trang_thai ?? '—' }}
                                </span>
                                @if($ls->ghi_chu)
                                    <span class="ms-2 text-muted" style="font-size:.85rem;">
                                        — {{ $ls->ghi_chu }}
                                    </span>
                                @endif
                                @if($ls->nguoiThayDoi)
                                    <div style="font-size:.78rem; color:#94a3b8; margin-top:4px;">
                                        <i class="bi bi-person me-1"></i>{{ $ls->nguoiThayDoi->name }}
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted text-end" style="white-space:nowrap; font-size:.82rem;">
                                {{ $ls->thoi_diem ? $ls->thoi_diem->format('H:i') : '—' }}<br>
                                <span style="color:#cbd5e1;">{{ $ls->thoi_diem ? $ls->thoi_diem->format('d/m/Y') : '' }}</span>
                            </small>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ══ CỘT PHẢI: Cập nhật trạng thái + Khách hàng + Tài xế ══════════════ --}}
    <div class="col-lg-4">

        {{-- ── FORM CẬP NHẬT TRẠNG THÁI (Phần 4) ─────────────────────────── --}}
        <div class="data-table-wrapper mb-4" style="border: 2px solid #3b82f6;">
            <div class="table-header" style="background: linear-gradient(135deg,#3b82f6,#6366f1); border-radius:12px 12px 0 0;">
                <h5 class="text-white mb-0">
                    <i class="bi bi-arrow-repeat me-2"></i>Cập nhật trạng thái
                </h5>
            </div>
            <div class="p-4">
                @if($errors->has('trang_thai_id'))
                <div class="alert alert-danger rounded-3 py-2 mb-3" style="font-size:.85rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first('trang_thai_id') }}
                </div>
                @endif

                {{-- POST /admin/don-hang/{id}/update-status --}}
                <form action="{{ route('admin.don_hang.update_status', $donHang) }}" method="POST">
                    @csrf

                    {{-- Dropdown chọn trạng thái --}}
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.85rem;">
                            Trạng thái mới <span class="text-danger">*</span>
                        </label>
                        <select name="trang_thai_id" class="form-select @error('trang_thai_id') is-invalid @enderror"
                                style="font-size:.9rem;" required>
                            <option value="">— Chọn trạng thái —</option>
                            @foreach($trangThais as $tt)
                                <option value="{{ $tt->id }}"
                                    {{ $donHang->trang_thai_id == $tt->id ? 'selected' : '' }}>
                                    {{ $tt->ten_trang_thai }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Ghi chú lý do --}}
                    <div class="mb-3">
                        <label class="form-label fw-600" style="font-size:.85rem;">Ghi chú / Lý do</label>
                        <textarea name="ghi_chu" class="form-control" rows="2"
                                  style="font-size:.88rem;"
                                  placeholder="VD: Tài xế đã lấy hàng, đang trên đường giao...">{{ old('ghi_chu') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-3" style="font-weight:600;">
                        <i class="bi bi-check2-circle me-2"></i>Cập nhật trạng thái
                    </button>
                </form>
            </div>
        </div>

        {{-- Khách hàng --}}
        <div class="data-table-wrapper mb-4">
            <div class="table-header">
                <h5><i class="bi bi-person me-2 text-success"></i>Khách hàng</h5>
            </div>
            <div class="p-4">
                @if($donHang->khachHang)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold"
                         style="width:46px;height:46px;font-size:1.1rem;flex-shrink:0;">
                        {{ strtoupper(mb_substr($donHang->khachHang->ten_khach, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $donHang->khachHang->ten_khach }}</div>
                        <div class="text-muted" style="font-size:.85rem;">
                            <i class="bi bi-telephone me-1"></i>{{ $donHang->khachHang->so_dien_thoai }}
                        </div>
                    </div>
                </div>
                <div class="info-label">Địa chỉ giao hàng</div>
                <div class="info-value" style="font-size:.9rem; line-height:1.5;">
                    <i class="bi bi-geo-alt me-1 text-danger"></i>{{ $donHang->khachHang->dia_chi }}
                </div>
                @else
                <p class="text-muted mb-0">Không có thông tin khách hàng.</p>
                @endif
            </div>
        </div>

        {{-- Shop gửi hàng --}}
        <div class="data-table-wrapper mb-4">
            <div class="table-header" style="background:linear-gradient(135deg,#EDE9FE,#DDD6FE);">
                <h5 class="text-purple-800 mb-0" style="color:#5B21B6;">
                    <i class="bi bi-shop me-2"></i>Thông tin Shop gửi hàng
                </h5>
            </div>
            <div class="p-4">
                @if($donHang->shop)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:46px;height:46px;flex-shrink:0;background:linear-gradient(135deg,#6C63FF,#A29BFE);">
                        <span class="text-white fw-bold fs-5">{{ strtoupper(mb_substr($donHang->shop->ten_shop, 0, 1)) }}</span>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $donHang->shop->ten_shop }}</div>
                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $donHang->shop->so_dien_thoai ?? '—' }}</small>
                    </div>
                </div>
                <div class="row g-2" style="font-size:.88rem;">
                    <div class="col-12">
                        <span class="info-label">Địa chỉ:</span>
                        <span class="info-value ms-1">{{ $donHang->shop->dia_chi ?? '—' }}</span>
                    </div>
                    @if($donHang->shop->bank_name)
                    <div class="col-12 mt-2 pt-2 border-top">
                        <div class="info-label mb-1">Thông tin Ngân hàng (đối soát)</div>
                        <div><i class="bi bi-bank me-1"></i>{{ $donHang->shop->bank_name }}</div>
                        <div><i class="bi bi-credit-card me-1"></i>{{ $donHang->shop->bank_account_number }} — {{ $donHang->shop->bank_account_name }}</div>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Đơn hàng không có thông tin Shop gửi.</p>
                @endif
            </div>
        </div>

        {{-- Tài xế --}}
        <div class="data-table-wrapper">
            <div class="table-header">
                <h5><i class="bi bi-person-badge me-2 text-primary"></i>Tài xế phụ trách</h5>
            </div>
            <div class="p-4">
                @if($donHang->taiXe)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                         style="width:46px;height:46px;font-size:1.1rem;flex-shrink:0;">
                        {{ strtoupper(mb_substr($donHang->taiXe->ho_ten, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $donHang->taiXe->ho_ten }}</div>
                        <span class="status-badge {{ $donHang->taiXe->trang_thai_class }}">
                            {{ $donHang->taiXe->trang_thai_label }}
                        </span>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="info-label">Số điện thoại</div>
                        <div class="info-value" style="font-size:.9rem;">{{ $donHang->taiXe->so_dien_thoai ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="info-label">Biển số xe</div>
                        <div class="info-value">
                            @if($donHang->taiXe->bien_so_xe)
                                <span class="badge bg-light text-dark border">{{ $donHang->taiXe->bien_so_xe }}</span>
                            @else —
                            @endif
                        </div>
                    </div>
                </div>
                @else
                <p class="text-muted mb-0"><i class="bi bi-exclamation-circle me-1"></i>Chưa phân công tài xế.</p>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
