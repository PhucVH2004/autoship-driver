<x-guest-layout>
@section('page_title', 'Đăng ký tài khoản')

<div class="auth-card">

    {{-- ── Brand ─────────────────────────────────────────────── --}}
    <div class="brand-logo">
        <i class="bi bi-truck"></i>
    </div>
    <div class="brand-title">Tạo tài khoản</div>
    <div class="brand-subtitle">Điền thông tin để bắt đầu sử dụng hệ thống</div>

    {{-- ── Error chung ─────────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="alert-custom">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ── Form đăng ký ────────────────────────────────────────── --}}
    <form method="POST" action="{{ route('register') }}" autocomplete="off">
        @csrf

        {{-- Name --}}
        <div class="mb-1">
            <label class="form-label-custom" for="name">Họ và tên</label>
            <div class="input-group-custom">
                <i class="bi bi-person input-icon"></i>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control-custom"
                    placeholder="Nguyễn Văn A"
                    required
                    autofocus
                    autocomplete="name"
                >
            </div>
            @error('name')
                <div class="field-error"><i class="bi bi-x-circle me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="mb-1 mt-3">
            <label class="form-label-custom" for="email">Email</label>
            <div class="input-group-custom">
                <i class="bi bi-envelope input-icon"></i>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control-custom"
                    placeholder="ten@example.com"
                    required
                    autocomplete="username"
                >
            </div>
            @error('email')
                <div class="field-error"><i class="bi bi-x-circle me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-1 mt-3">
            <label class="form-label-custom" for="password">Mật khẩu</label>
            <div class="input-group-custom">
                <i class="bi bi-lock input-icon"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control-custom"
                    placeholder="Ít nhất 8 ký tự"
                    required
                    autocomplete="new-password"
                    style="padding-right: 2.8rem;"
                >
                <button type="button" class="toggle-pass" id="togglePass1Btn" title="Hiện/ẩn mật khẩu">
                    <i class="bi bi-eye" id="togglePass1Icon"></i>
                </button>
            </div>
            @error('password')
                <div class="field-error"><i class="bi bi-x-circle me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div class="mb-1 mt-3">
            <label class="form-label-custom" for="password_confirmation">Xác nhận mật khẩu</label>
            <div class="input-group-custom">
                <i class="bi bi-shield-lock input-icon"></i>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control-custom"
                    placeholder="Nhập lại mật khẩu"
                    required
                    autocomplete="new-password"
                    style="padding-right: 2.8rem;"
                >
                <button type="button" class="toggle-pass" id="togglePass2Btn" title="Hiện/ẩn mật khẩu">
                    <i class="bi bi-eye" id="togglePass2Icon"></i>
                </button>
            </div>
            @error('password_confirmation')
                <div class="field-error"><i class="bi bi-x-circle me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-auth mt-4" id="registerBtn">
            <i class="bi bi-person-plus me-2"></i>Tạo tài khoản
        </button>
    </form>

    {{-- ── Footer link ─────────────────────────────────────────── --}}
    <div class="auth-footer">
        Đã có tài khoản?
        <a href="{{ route('login') }}">Đăng nhập ngay</a>
    </div>

</div>

<script>
    (function () {
        // Toggle password 1
        const btn1  = document.getElementById('togglePass1Btn');
        const icon1 = document.getElementById('togglePass1Icon');
        const inp1  = document.getElementById('password');
        if (btn1) {
            btn1.addEventListener('click', function () {
                const isPass = inp1.type === 'password';
                inp1.type    = isPass ? 'text' : 'password';
                icon1.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        // Toggle password 2
        const btn2  = document.getElementById('togglePass2Btn');
        const icon2 = document.getElementById('togglePass2Icon');
        const inp2  = document.getElementById('password_confirmation');
        if (btn2) {
            btn2.addEventListener('click', function () {
                const isPass = inp2.type === 'password';
                inp2.type    = isPass ? 'text' : 'password';
                icon2.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        // Loading state on submit
        const form = document.querySelector('form');
        const regBtn = document.getElementById('registerBtn');
        if (form && regBtn) {
            form.addEventListener('submit', function () {
                regBtn.disabled = true;
                regBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang tạo tài khoản...';
            });
        }
    })();
</script>
</x-guest-layout>
