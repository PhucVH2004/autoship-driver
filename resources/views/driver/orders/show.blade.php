@extends('layouts.driver')

@section('page_title', 'Chi tiết đơn hàng')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">
            <span class="title-icon" style="background:linear-gradient(135deg,#4F8EF7,#667EEA);">
                <i class="bi bi-file-text"></i>
            </span>
            Chi tiết đơn hàng
        </h1>
        <p class="page-subtitle">
            Mã đơn: <strong style="color:#4F8EF7;">#{{ $donHang->ma_don ?? 'N/A' }}</strong>
        </p>
    </div>
    <a href="{{ route('driver.orders') }}" class="back-btn">
        <i class="bi bi-arrow-left"></i> Quay lại
    </a>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="border-radius:12px;border:none;background:rgba(34,211,160,.12);color:#0C9B73;">
    <i class="bi bi-check-circle-fill fs-5"></i>
    {{ session('success') }}
</div>
@endif

{{-- ── QUICK ACTION BUTTONS (YC3) ──────────────────── --}}
<div class="driver-card mb-4">
    <div class="driver-card-header">
        <i class="bi bi-lightning-charge-fill" style="color:#FF6B2B;"></i>
        Cập nhật nhanh trạng thái
    </div>
    <div class="driver-card-body">
        <div class="row g-2">
            @foreach($trangThaiOptions as $tt)
            @php
                $isActive = $donHang->trang_thai_id == $tt->id;
                
                $btnStyle = match($tt->id) {
                    \App\Models\TrangThaiDonHang::DA_LAY_HANG => ['bg'=>'#3182CE','shadow'=>'rgba(49,130,206,.3)'],
                    \App\Models\TrangThaiDonHang::DANG_GIAO   => ['bg'=>'#4F8EF7','shadow'=>'rgba(79,142,247,.3)'],
                    \App\Models\TrangThaiDonHang::DA_GIAO     => ['bg'=>'#22d3a0','shadow'=>'rgba(34,211,160,.3)'],
                    \App\Models\TrangThaiDonHang::HUY         => ['bg'=>'#F56565','shadow'=>'rgba(245,101,101,.3)'],
                    \App\Models\TrangThaiDonHang::HOAN        => ['bg'=>'#F6AD55','shadow'=>'rgba(246,173,85,.3)'],
                    \App\Models\TrangThaiDonHang::DA_HOAN     => ['bg'=>'#805AD5','shadow'=>'rgba(128,90,213,.3)'],
                    default                                     => ['bg'=>'#A0AEC0','shadow'=>'rgba(160,174,192,.3)'],
                };

                $btnIcon = match($tt->id) {
                    \App\Models\TrangThaiDonHang::DA_LAY_HANG => 'box-seam',
                    \App\Models\TrangThaiDonHang::DANG_GIAO   => 'truck',
                    \App\Models\TrangThaiDonHang::DA_GIAO     => 'check2-circle',
                    \App\Models\TrangThaiDonHang::HUY         => 'x-circle',
                    \App\Models\TrangThaiDonHang::HOAN        => 'arrow-counterclockwise',
                    \App\Models\TrangThaiDonHang::DA_HOAN     => 'archive',
                    default                                     => 'arrow-repeat',
                };
            @endphp
            <div class="col-6 col-md-3">
                <form action="{{ route('driver.orders.update_status', $donHang->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="trang_thai_id" value="{{ $tt->id }}">
                    <button type="submit"
                        class="quick-status-btn {{ $isActive ? 'active' : '' }}"
                        style="--qbtn-color:{{ $btnStyle['bg'] }};--qbtn-shadow:{{ $btnStyle['shadow'] }};"
                        {{ $isActive ? 'disabled' : '' }}>
                        <i class="bi bi-{{ $btnIcon }}"></i>
                        {{ $tt->ten_trang_thai }}
                        @if($isActive)
                        <span class="qbtn-active-dot"></span>
                        @endif
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Hoặc chọn từ danh sách --}}
        <div class="mt-3">
            <button type="button" class="btn w-100" data-bs-toggle="collapse" data-bs-target="#advancedStatus"
                    style="background:#F7FAFC;border:1px solid #E2E8F0;border-radius:10px;color:#718096;font-size:.85rem;">
                <i class="bi bi-sliders me-1"></i> Cập nhật với ghi chú
            </button>
            <div id="advancedStatus" class="collapse mt-2">
                <form action="{{ route('driver.orders.update_status', $donHang->id) }}" method="POST"
                      class="p-3 rounded-3" style="background:#F7FAFC;border:1px solid #E2E8F0;">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.82rem;font-weight:600;color:#A0AEC0;text-transform:uppercase;">Trạng thái mới</label>
                        <select name="trang_thai_id" class="form-select" style="border-radius:10px;">
                            @foreach($trangThaiOptions as $tt)
                            <option value="{{ $tt->id }}" {{ $donHang->trang_thai_id == $tt->id ? 'selected' : '' }}>
                                {{ $tt->ten_trang_thai }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.82rem;font-weight:600;color:#A0AEC0;text-transform:uppercase;">Ghi chú</label>
                        <textarea name="ghi_chu" class="form-control" rows="2"
                                  placeholder="Ghi chú thêm cho quản lý..."
                                  style="border-radius:10px;font-size:.88rem;"></textarea>
                    </div>
                    <button type="submit" class="btn w-100"
                            style="background:linear-gradient(135deg,#FF6B2B,#FF9A5C);color:#fff;border-radius:10px;font-weight:600;">
                        <i class="bi bi-arrow-repeat me-1"></i>Cập nhật trạng thái
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ── LEFT COLUMN ──────────────────── --}}
    <div class="col-lg-7">

        {{-- Trạng thái + Timeline --}}
        <div class="driver-card mb-4">
            <div class="driver-card-header">
                <i class="bi bi-activity" style="color:#FF6B2B;"></i>
                Lịch sử trạng thái
            </div>
            <div class="driver-card-body">
                @php
                    $tenTT = $donHang->trangThai?->ten_trang_thai ?? 'Chưa xác định';
                    $maMau = $donHang->trangThai?->ma_mau ?? '#718096';
                @endphp

                <div class="current-status-block mb-4">
                    <div class="cs-label">Trạng thái hiện tại</div>
                    <div class="cs-badge" style="--tt-color: {{ $maMau }};">
                        <span class="cs-dot" style="background:{{ $maMau }};"></span>
                        {{ $tenTT }}
                    </div>
                </div>

                @if(!empty($donHang->lichSuTrangThais) && count($donHang->lichSuTrangThais) > 0)
                <div class="timeline-label">Lịch sử cập nhật</div>
                <div class="status-timeline">
                    @foreach($donHang->lichSuTrangThais as $ls)
                    <div class="timeline-item {{ $loop->first ? 'is-current' : '' }}">
                        <div class="tl-dot"></div>
                        <div class="tl-content">
                            <div class="tl-status">{{ $ls->trangThai?->ten_trang_thai ?? '—' }}</div>
                            <div class="tl-meta">
                                <i class="bi bi-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($ls->thoi_diem)->format('d/m/Y H:i') }}
                                @if($ls->ghi_chu)
                                    · {{ $ls->ghi_chu }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-muted text-center py-3" style="font-size:.85rem;">
                    <i class="bi bi-clock-history d-block mb-1 fs-4"></i>
                    Chưa có lịch sử cập nhật
                </div>
                @endif
            </div>
        </div>

        {{-- Upload ảnh xác nhận (YC7) --}}
        <div class="driver-card mb-4">
            <div class="driver-card-header">
                <i class="bi bi-camera-fill" style="color:#A78BFA;"></i>
                Ảnh xác nhận giao hàng
            </div>
            <div class="driver-card-body">
                @if($donHang->delivery_photo)
                {{-- Hiển thị ảnh đã upload --}}
                <div class="mb-3">
                    <img src="{{ asset('storage/' . $donHang->delivery_photo) }}"
                         alt="Ảnh giao hàng"
                         style="width:100%;max-height:300px;object-fit:cover;border-radius:12px;border:2px solid #E2E8F0;">
                    <div class="mt-2 text-center" style="font-size:.78rem;color:#A0AEC0;">
                        <i class="bi bi-check2-circle text-success me-1"></i>
                        Đã có ảnh xác nhận
                    </div>
                </div>
                @endif

                {{-- Form upload ảnh --}}
                <form action="{{ route('driver.orders.upload_photo', $donHang->id) }}"
                      method="POST" enctype="multipart/form-data" id="photoUploadForm">
                    @csrf
                    <div class="photo-upload-area" id="dropZone"
                         onclick="document.getElementById('photoInput').click()"
                         ondragover="event.preventDefault();this.style.borderColor='#A78BFA';"
                         ondragleave="this.style.borderColor='#E2E8F0';"
                         ondrop="handleDrop(event)">
                        <div id="dropContent">
                            <i class="bi bi-cloud-upload fs-2 mb-2" style="color:#A78BFA;"></i>
                            <div style="font-size:.88rem;font-weight:600;color:#2D3748;">
                                {{ $donHang->delivery_photo ? 'Thay ảnh mới' : 'Upload ảnh giao hàng' }}
                            </div>
                            <div style="font-size:.76rem;color:#A0AEC0;margin-top:.25rem;">
                                JPEG, PNG, WebP • Tối đa 5MB
                            </div>
                        </div>
                        <img id="previewImg" src="" alt="" style="display:none;width:100%;max-height:200px;object-fit:cover;border-radius:8px;">
                    </div>
                    <input type="file" name="delivery_photo" id="photoInput" accept="image/*"
                           style="display:none;" onchange="previewPhoto(this)">

                    @error('delivery_photo')
                    <div class="text-danger mt-2" style="font-size:.82rem;">{{ $message }}</div>
                    @enderror

                    <button type="submit" id="uploadBtn" class="btn w-100 mt-3" style="display:none;
                            background:linear-gradient(135deg,#A78BFA,#7C3AED);color:#fff;border-radius:10px;font-weight:600;">
                        <i class="bi bi-upload me-1"></i>Xác nhận upload ảnh
                    </button>
                </form>
            </div>
        </div>

        {{-- Ghi chú --}}
        @if($donHang->ghi_chu ?? false)
        <div class="driver-card mb-4">
            <div class="driver-card-header">
                <i class="bi bi-sticky" style="color:#F6AD55;"></i>
                Ghi chú cho tài xế
            </div>
            <div class="driver-card-body">
                <div class="note-block">
                    <i class="bi bi-info-circle-fill me-2" style="color:#F6AD55;"></i>
                    {{ $donHang->ghi_chu }}
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── RIGHT COLUMN ─────────────────── --}}
    <div class="col-lg-5">

        {{-- Thông tin khách hàng --}}
        <div class="driver-card mb-4">
            <div class="driver-card-header">
                <i class="bi bi-person-circle" style="color:#22d3a0;"></i>
                Thông tin khách hàng
            </div>
            <div class="driver-card-body">
                @php $kh = $donHang->khachHang ?? null; @endphp

                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="kh-avatar">
                        {{ strtoupper(substr($kh?->ten_khach ?? 'K', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:1rem;font-weight:700;color:#1A202C;">
                            {{ $kh?->ten_khach ?? '—' }}
                        </div>
                        <div style="font-size:.8rem;color:#A0AEC0;">Khách hàng</div>
                    </div>
                </div>

                <div class="info-rows">
                    <div class="info-row">
                        <div class="info-icon" style="background:rgba(34,211,160,.1);color:#22d3a0;">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div>
                            <div class="info-label">Số điện thoại</div>
                            <div class="info-value">
                                <a href="tel:{{ $kh?->so_dien_thoai }}" style="color:#2D3748;text-decoration:none;font-weight:600;">
                                    {{ $kh?->so_dien_thoai ?? '—' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon" style="background:rgba(255,107,43,.1);color:#FF6B2B;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <div class="info-label">Địa chỉ giao hàng</div>
                            <div class="info-value">{{ $kh?->dia_chi ?? '—' }}</div>
                        </div>
                    </div>

                    @if($kh?->latitude && $kh?->longitude)
                    <div class="info-row">
                        <div class="info-icon" style="background:rgba(79,142,247,.1);color:#4F8EF7;">
                            <i class="bi bi-pin-map-fill"></i>
                        </div>
                        <div>
                            <div class="info-label">Toạ độ GPS</div>
                            <div class="info-value" style="font-size:.82rem;font-family:monospace;">
                                {{ $kh->latitude }}, {{ $kh->longitude }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- CTA Buttons --}}
                @php
                    // Xây URL chỉ đường: ưu tiên GPS, fallback địa chỉ văn bản
                    if ($kh?->latitude && $kh?->longitude) {
                        $navDestUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . $kh->latitude . ',' . $kh->longitude;
                    } elseif ($kh?->dia_chi) {
                        $navDestUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($kh->dia_chi);
                    } else {
                        $navDestUrl = null;
                    }
                @endphp
                <div class="d-flex gap-2 mt-4">
                    @if($kh?->so_dien_thoai)
                    <a href="tel:{{ $kh->so_dien_thoai }}" class="call-btn flex-fill d-flex">
                        <i class="bi bi-telephone-fill me-2"></i>
                        Gọi khách hàng
                    </a>
                    @endif
                    {{-- Nút Chỉ đường — luôn hiển thị nếu có địa chỉ (YC2) --}}
                    @if($navDestUrl)
                    <a href="{{ $navDestUrl }}"
                       target="_blank"
                       class="flex-fill d-flex align-items-center justify-content-center gap-1"
                       style="background:linear-gradient(135deg,#22d3a0,#0EA5E9);color:#fff;border-radius:10px;padding:.7rem 1rem;font-size:.88rem;font-weight:600;text-decoration:none;transition:all .2s;"
                       onmouseover="this.style.boxShadow='0 6px 18px rgba(34,211,160,.4)'"
                       onmouseout="this.style.boxShadow='none'"
                       title="Chỉ đường tới: {{ $kh?->dia_chi ?? '' }}">
                        <i class="bi bi-send-fill me-1"></i>
                        Chỉ đường
                    </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Thông tin đơn hàng --}}
        <div class="driver-card">
            <div class="driver-card-header">
                <i class="bi bi-receipt" style="color:#4F8EF7;"></i>
                Chi tiết đơn hàng
            </div>
            <div class="driver-card-body">
                <div class="detail-rows">
                    <div class="detail-row">
                        <span class="detail-label">Mã đơn</span>
                        <span class="detail-value" style="font-family:monospace;color:#4F8EF7;font-weight:700;">
                            #{{ $donHang->ma_don ?? '—' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Tổng tiền</span>
                        <span class="detail-value" style="color:#22d3a0;font-weight:700;">
                            {{ $donHang->tong_tien ? number_format($donHang->tong_tien, 0, ',', '.') . ' đ' : '—' }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Dự kiến giao</span>
                        <span class="detail-value">
                            {{ $donHang->thoi_gian_giao_du_kien
                                ? \Carbon\Carbon::parse($donHang->thoi_gian_giao_du_kien)->format('d/m/Y H:i')
                                : '—' }}
                        </span>
                    </div>
                    @if($donHang->thoi_gian_hoan_thanh)
                    <div class="detail-row">
                        <span class="detail-label">Hoàn thành lúc</span>
                        <span class="detail-value" style="color:#22d3a0;">
                            {{ \Carbon\Carbon::parse($donHang->thoi_gian_hoan_thanh)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Tạo lúc</span>
                        <span class="detail-value">
                            {{ \Carbon\Carbon::parse($donHang->created_at)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @if($donHang->delivery_photo)
                    <div class="detail-row">
                        <span class="detail-label">Ảnh xác nhận</span>
                        <span class="detail-value">
                            <a href="{{ asset('storage/' . $donHang->delivery_photo) }}" target="_blank"
                               style="color:#A78BFA;font-size:.8rem;">
                                <i class="bi bi-image me-1"></i>Xem ảnh
                            </a>
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Breakdown phí (logistics-style) --}}
        <div class="driver-card mt-4">
            <div class="driver-card-header">
                <i class="bi bi-cash-coin" style="color:#22d3a0;"></i>
                Chi tiết phí giao hàng
            </div>
            <div class="driver-card-body">
                @include('components.fee-breakdown', ['donHang' => $donHang, 'fee' => $fee, 'role' => 'driver'])

                @if($donHang->trang_thai_id === \App\Models\TrangThaiDonHang::DA_GIAO)
                    <div class="text-muted mt-2" style="font-size:.78rem;">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>Đơn đã giao thành công: phí đã được chốt &amp; lưu để đối soát.
                    </div>
                @else
                    <div class="text-muted mt-2" style="font-size:.78rem;">
                        <i class="bi bi-info-circle me-1"></i>Phí hiển thị được tính tại thời điểm tạo đơn.
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
.back-btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .55rem 1.15rem; background: #fff;
    border: 1px solid #E2E8F0; border-radius: 20px;
    color: #4A5568; text-decoration: none; font-size: .85rem; font-weight: 500; transition: all .2s;
}
.back-btn:hover { background: #F7FAFC; color: #2D3748; box-shadow: 0 3px 10px rgba(0,0,0,.08); }

/* Quick Status Buttons (YC3) */
.quick-status-btn {
    width: 100%; padding: .7rem .5rem;
    border-radius: 10px; border: 2px solid var(--qbtn-color);
    background: color-mix(in srgb, var(--qbtn-color) 10%, transparent);
    color: var(--qbtn-color); font-size: .8rem; font-weight: 700;
    cursor: pointer; transition: all .2s; position: relative;
    display: flex; align-items: center; justify-content: center; gap: .35rem;
}
.quick-status-btn:hover:not(:disabled) {
    background: var(--qbtn-color); color: #fff;
    box-shadow: 0 4px 14px var(--qbtn-shadow);
    transform: translateY(-1px);
}
.quick-status-btn.active {
    background: var(--qbtn-color); color: #fff;
    box-shadow: 0 4px 14px var(--qbtn-shadow);
    cursor: default;
}
.quick-status-btn:disabled { opacity: .7; }
.qbtn-active-dot {
    width: 6px; height: 6px; border-radius: 50%; background: #fff;
    position: absolute; top: 6px; right: 6px;
    animation: pulse-dot 1.5s infinite;
}

/* Photo Upload Area */
.photo-upload-area {
    border: 2px dashed #E2E8F0; border-radius: 12px; padding: 1.5rem;
    text-align: center; cursor: pointer; transition: all .2s;
    background: #FAFBFF;
}
.photo-upload-area:hover { border-color: #A78BFA; background: #FAF5FF; }

/* Current status */
.current-status-block { }
.cs-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #A0AEC0; margin-bottom: .5rem; }
.cs-badge {
    display: inline-flex; align-items: center; gap: .6rem;
    padding: .5rem 1.1rem;
    background: color-mix(in srgb, var(--tt-color) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--tt-color) 30%, transparent);
    border-radius: 20px; font-weight: 700; font-size: .95rem; color: var(--tt-color);
}
.cs-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; animation: pulse-dot 2s infinite; }

/* Timeline */
.timeline-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #A0AEC0; margin-bottom: .75rem; }
.status-timeline { position: relative; padding-left: 1.5rem; }
.status-timeline::before { content: ''; position: absolute; left: 7px; top: 8px; bottom: 0; width: 2px; background: #E2E8F0; }
.timeline-item { position: relative; padding-bottom: 1.1rem; padding-left: .75rem; }
.timeline-item:last-child { padding-bottom: 0; }
.tl-dot { width: 14px; height: 14px; border-radius: 50%; background: #CBD5E0; border: 2px solid #fff; box-shadow: 0 0 0 2px #CBD5E0; position: absolute; left: -1.6rem; top: 3px; }
.timeline-item.is-current .tl-dot { background: #FF6B2B; box-shadow: 0 0 0 2px #FF6B2B; animation: pulse-dot 2s infinite; }
.tl-status { font-size: .88rem; font-weight: 600; color: #2D3748; }
.tl-meta   { font-size: .76rem; color: #A0AEC0; margin-top: .15rem; }

/* Note */
.note-block { background: #FFFBF0; border: 1px solid #FBD38D; border-radius: 10px; padding: .9rem 1rem; font-size: .88rem; color: #744210; display: flex; align-items: flex-start; gap: .5rem; }

/* Customer info */
.kh-avatar { width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #22d3a0, #0EA5E9); color: #fff; font-weight: 800; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.info-rows { display: flex; flex-direction: column; gap: .85rem; }
.info-row  { display: flex; align-items: flex-start; gap: .85rem; }
.info-icon { width: 36px; height: 36px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .95rem; flex-shrink: 0; }
.info-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #A0AEC0; }
.info-value { font-size: .88rem; color: #2D3748; margin-top: .1rem; }

.call-btn { background: linear-gradient(135deg, #22d3a0, #0EA5E9); color: #fff; border: none; border-radius: 10px; padding: .7rem 1.25rem; font-size: .88rem; font-weight: 600; text-decoration: none; cursor: pointer; align-items: center; justify-content: center; transition: all .2s; }
.call-btn:hover { color: #fff; box-shadow: 0 6px 18px rgba(34,211,160,.4); transform: translateY(-1px); }

/* Detail rows */
.detail-rows { display: flex; flex-direction: column; gap: 0; }
.detail-row { display: flex; justify-content: space-between; align-items: center; padding: .7rem 0; border-bottom: 1px solid #F0F4F8; }
.detail-row:last-child { border-bottom: none; }
.detail-label { font-size: .82rem; color: #A0AEC0; font-weight: 500; }
.detail-value { font-size: .85rem; color: #2D3748; font-weight: 600; text-align: right; }
</style>
@endpush

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewImg').style.display = 'block';
            document.getElementById('dropContent').style.display = 'none';
            document.getElementById('uploadBtn').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleDrop(event) {
    event.preventDefault();
    document.getElementById('dropZone').style.borderColor = '#E2E8F0';
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        const input = document.getElementById('photoInput');
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;
        previewPhoto(input);
    }
}
</script>
@endpush
