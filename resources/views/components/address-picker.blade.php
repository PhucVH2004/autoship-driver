{{--
    Component: address-picker (Local DB + Nominatim Geocoding)

    Props:
      $prefix       — Prefix tên field (VD: "kh", "shop")
      $tinhId       — ID Tỉnh cũ (khi edit)
      $quanId       — ID Quận cũ (khi edit)
      $xaId         — ID Xã cũ (khi edit)
      $diaChiCuThe  — Số nhà, tên đường (khi edit)
      $latField     — Tên hidden input lat (mặc định: "latitude")
      $lngField     — Tên hidden input lng (mặc định: "longitude")
      $required     — true/false
--}}
@php
    $prefix      = $prefix      ?? 'addr';
    $tinhId      = $tinhId      ?? old($prefix . '_tinh_id');
    $quanId      = $quanId      ?? old($prefix . '_quan_id');
    $xaId        = $xaId        ?? old($prefix . '_xa_id');
    $diaChiCuThe = $diaChiCuThe ?? old($prefix . '_dia_chi_cu_the');
    $latField    = $latField    ?? 'latitude';
    $lngField    = $lngField    ?? 'longitude';
    $required    = $required    ?? false;
    $reqAttr     = $required ? 'required' : '';
@endphp

{{-- ── Row 1: Tỉnh / Quận ──────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label class="form-label fw-600">
            Tỉnh / Thành phố @if($required)<span class="text-danger">*</span>@endif
        </label>
        <select id="{{ $prefix }}_tinh"
                name="{{ $prefix }}_tinh_id"
                class="form-select @error($prefix . '_tinh_id') is-invalid @enderror"
                {{ $reqAttr }}>
            <option value="">— Đang tải... —</option>
        </select>
        @error($prefix . '_tinh_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-600">
            Quận / Huyện @if($required)<span class="text-danger">*</span>@endif
        </label>
        <select id="{{ $prefix }}_quan"
                name="{{ $prefix }}_quan_id"
                class="form-select @error($prefix . '_quan_id') is-invalid @enderror"
                {{ $reqAttr }}
                disabled>
            <option value="">— Chọn Tỉnh/TP trước —</option>
        </select>
        @error($prefix . '_quan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ── Row 2: Xã / Đường ───────────────────────────────────────── --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <label class="form-label fw-600">
            Xã / Phường @if($required)<span class="text-danger">*</span>@endif
        </label>
        <select id="{{ $prefix }}_xa"
                name="{{ $prefix }}_xa_id"
                class="form-select @error($prefix . '_xa_id') is-invalid @enderror"
                {{ $reqAttr }}
                disabled>
            <option value="">— Chọn Quận/Huyện trước —</option>
        </select>
        @error($prefix . '_xa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-8">
        <label class="form-label fw-600">Số nhà, Tên đường</label>
        <div class="input-group">
            <input type="text"
                   name="{{ $prefix }}_dia_chi_cu_the"
                   id="{{ $prefix }}_dia_chi_cu_the"
                   class="form-control @error($prefix . '_dia_chi_cu_the') is-invalid @enderror"
                   placeholder="VD: 123 Nguyễn Huệ"
                   value="{{ $diaChiCuThe }}">
            <button type="button"
                    class="btn btn-outline-secondary"
                    id="{{ $prefix }}_geocode_btn"
                    onclick="addrPicker_{{ $prefix }}.doGeocode()"
                    title="Tự động lấy toạ độ GPS từ địa chỉ">
                <i class="bi bi-geo-fill"></i>
            </button>
        </div>
        <div class="form-text">Nhấn <i class="bi bi-geo-fill"></i> để tự lấy toạ độ GPS (dùng cho lộ trình tài xế).</div>
        @error($prefix . '_dia_chi_cu_the')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

{{-- ── Preview địa chỉ + toạ độ ───────────────────────────────── --}}
<div id="{{ $prefix }}_addr_preview"
     class="rounded-3 px-3 py-2 mb-2 d-none"
     style="background:#F0FDF4;border:1px solid #BBF7D0;font-size:.82rem;">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-geo-alt-fill text-success"></i>
        <span id="{{ $prefix }}_addr_text" class="text-success fw-500"></span>
        <span id="{{ $prefix }}_coords_badge"
              class="ms-auto badge d-none"
              style="background:#D1FAE5;color:#065F46;font-size:.72rem;">
            <i class="bi bi-pin-map-fill me-1"></i>
            <span id="{{ $prefix }}_coords_text"></span>
        </span>
        <span id="{{ $prefix }}_geocoding_badge"
              class="ms-auto badge d-none"
              style="background:#FEF3C7;color:#92400E;font-size:.72rem;">
            <i class="bi bi-arrow-clockwise spin me-1"></i> Đang lấy toạ độ...
        </span>
    </div>
</div>

{{-- Hidden GPS inputs --}}
<input type="hidden" name="{{ $latField }}" id="{{ $prefix }}_lat" value="{{ old($latField) }}">
<input type="hidden" name="{{ $lngField }}" id="{{ $prefix }}_lng" value="{{ old($lngField) }}">

@push('extra_js')
<script>
(function () {
    const PREFIX = '{{ $prefix }}';
    const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const INIT_TINH = '{{ $tinhId }}';
    const INIT_QUAN = '{{ $quanId }}';
    const INIT_XA   = '{{ $xaId }}';

    const el = id => document.getElementById(PREFIX + '_' + id);

    // ── Điền <option> vào <select> ──────────────────────────────────
    function fillSelect(sel, items, placeholder, selectedId = null) {
        sel.innerHTML = `<option value="">${placeholder}</option>`;
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            if (selectedId && String(item.id) === String(selectedId)) opt.selected = true;
            sel.appendChild(opt);
        });
        sel.disabled = items.length === 0;
    }

    // ── Preview địa chỉ + badge toạ độ ─────────────────────────────
    function updatePreview() {
        const parts = [
            el('dia_chi_cu_the')?.value?.trim(),
            el('xa')?.selectedOptions[0]?.text,
            el('quan')?.selectedOptions[0]?.text,
            el('tinh')?.selectedOptions[0]?.text,
        ].filter(s => s && !s.startsWith('—'));

        const preview = el('addr_preview');
        if (parts.length >= 2) {
            el('addr_text').textContent = parts.join(', ');
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }

        const lat = el('lat')?.value;
        const lng = el('lng')?.value;
        const badge = el('coords_badge');
        if (lat && lng) {
            el('coords_text').textContent = `${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    // ── Xoá toạ độ ──────────────────────────────────────────────────
    function clearCoords() {
        if (el('lat')) el('lat').value = '';
        if (el('lng')) el('lng').value = '';
        el('coords_badge').classList.add('d-none');
    }

    // ── Load Quận theo Tỉnh ─────────────────────────────────────────
    async function loadQuan(selectedQuanId = null) {
        const tinhId = el('tinh')?.value;
        if (!tinhId) return;

        const quanSel = el('quan');
        quanSel.disabled = true;
        quanSel.innerHTML = '<option value="">Đang tải...</option>';

        const xaSel = el('xa');
        xaSel.innerHTML = '<option value="">— Chọn Quận/Huyện trước —</option>';
        xaSel.disabled = true;
        clearCoords();

        const res  = await fetch(`/api/address/districts?tinh=${tinhId}`);
        const data = await res.json();
        fillSelect(quanSel, data, '— Chọn Quận/Huyện —', selectedQuanId);

        if (selectedQuanId) await loadXa(INIT_XA);
    }

    // ── Load Xã theo Quận ───────────────────────────────────────────
    async function loadXa(selectedXaId = null) {
        const quanId = el('quan')?.value;
        if (!quanId) return;

        const xaSel = el('xa');
        xaSel.disabled = true;
        xaSel.innerHTML = '<option value="">Đang tải...</option>';
        clearCoords();

        const res  = await fetch(`/api/address/wards?quan=${quanId}`);
        const data = await res.json();
        fillSelect(xaSel, data, '— Chọn Xã/Phường —', selectedXaId);

        if (selectedXaId) updatePreview();
    }

    // ── Geocoding (Nominatim) ────────────────────────────────────────
    async function doGeocode() {
        const tinh  = el('tinh')?.selectedOptions[0]?.text ?? '';
        const quan  = el('quan')?.selectedOptions[0]?.text ?? '';
        const xa    = el('xa')?.selectedOptions[0]?.text ?? '';
        const duong = el('dia_chi_cu_the')?.value?.trim() ?? '';

        if (!tinh && !quan) {
            alert('Vui lòng chọn Tỉnh và Quận trước khi lấy toạ độ.');
            return;
        }

        const fullAddress = [duong, xa, quan, tinh].filter(Boolean).join(', ');

        el('geocoding_badge').classList.remove('d-none');
        el('coords_badge').classList.add('d-none');
        const btn = el('geocode_btn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>'; }

        try {
            const res = await fetch('/api/address/geocode', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ address: fullAddress }),
            });

            const preview = el('addr_preview');
            if (res.ok) {
                const data = await res.json();
                el('lat').value = data.lat;
                el('lng').value = data.lng;
                updatePreview();
                preview.style.background  = '#F0FDF4';
                preview.style.borderColor = '#BBF7D0';
                preview.classList.remove('d-none');
            } else {
                el('addr_text').textContent = '⚠️ Không tìm thấy toạ độ — địa chỉ vẫn được lưu bình thường.';
                preview.style.background  = '#FFF7ED';
                preview.style.borderColor = '#FED7AA';
                preview.classList.remove('d-none');
            }
        } catch (e) {
            console.error('Geocoding error', e);
        } finally {
            el('geocoding_badge').classList.add('d-none');
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-geo-fill"></i>'; }
        }
    }

    window['addrPicker_' + PREFIX] = { doGeocode };

    // ── Khởi động ───────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', async () => {
        const tinhSel = el('tinh');
        tinhSel.innerHTML = '<option value="">Đang tải...</option>';
        tinhSel.disabled = true;

        const res  = await fetch('/api/address/provinces');
        const list = await res.json();
        fillSelect(tinhSel, list, '— Chọn Tỉnh/TP —', INIT_TINH);

        tinhSel.addEventListener('change', () => loadQuan());
        el('quan')?.addEventListener('change', () => { loadXa(); clearCoords(); });
        el('xa')?.addEventListener('change', () => { updatePreview(); clearCoords(); });
        el('dia_chi_cu_the')?.addEventListener('input', updatePreview);

        if (INIT_TINH) await loadQuan(INIT_QUAN);

        updatePreview();
    });

    // Spin CSS (chỉ inject 1 lần)
    if (!document.getElementById('addr_spin_style')) {
        const s = document.createElement('style');
        s.id = 'addr_spin_style';
        s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}.spin{display:inline-block;animation:spin .6s linear infinite}';
        document.head.appendChild(s);
    }
})();
</script>
@endpush
