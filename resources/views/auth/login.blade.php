<x-guest-layout>
@section('page_title', 'Đăng nhập')

<div class="auth-card">

    {{-- ── Brand ─────────────────────────────────────────────── --}}
    <div class="brand-logo">
        <i class="bi bi-truck"></i>
    </div>
    <div class="brand-title">GiaoHang Pro</div>
    <div class="brand-subtitle">Quản lý lộ trình giao hàng thông minh</div>

    {{-- ── Session Status (success) ───────────────────────────── --}}
    @if (session('status'))
        <div class="alert-success-custom">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
        </div>
    @endif

    {{-- ── Error chung ─────────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="alert-custom">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- ── Form đăng nhập ─────────────────────────────────────── --}}
    <form method="POST" action="{{ route('login') }}" autocomplete="off">
        @csrf

        {{-- Email or Phone --}}
        <div class="mb-1">
            <label class="form-label-custom" for="login">Số điện thoại hoặc Email</label>
            <div class="input-group-custom">
                <i class="bi bi-person input-icon"></i>
                <input
                    id="login"
                    type="text"
                    name="login"
                    value="{{ old('login') }}"
                    class="form-control-custom"
                    placeholder="0912345678 hoặc admin@giaohang.com"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('login')
                <div class="field-error"><i class="bi bi-x-circle me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-1 mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label-custom" for="password">Mật khẩu</label>
                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        Quên mật khẩu?
                    </a>
                @endif
            </div>
            <div class="input-group-custom">
                <i class="bi bi-lock input-icon"></i>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control-custom"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                    style="padding-right: 2.8rem;"
                >
                <button type="button" class="toggle-pass" id="togglePassBtn" title="Hiện/ẩn mật khẩu">
                    <i class="bi bi-eye" id="togglePassIcon"></i>
                </button>
            </div>
            @error('password')
                <div class="field-error"><i class="bi bi-x-circle me-1"></i>{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="mt-3">
            <label class="custom-check">
                <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                <span>Ghi nhớ đăng nhập</span>
            </label>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-auth mt-4" id="loginBtn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
        </button>
    </form>

    {{-- ── Footer link ─────────────────────────────────────────── --}}
    <div class="auth-footer">
        Chưa có tài khoản?
        <a href="{{ route('register') }}">Đăng ký ngay</a>
    </div>

</div>

<script>
    // Toggle password
    (function () {
        const btn   = document.getElementById('togglePassBtn');
        const icon  = document.getElementById('togglePassIcon');
        const input = document.getElementById('password');
        if (btn) {
            btn.addEventListener('click', function () {
                const isPass = input.type === 'password';
                input.type   = isPass ? 'text' : 'password';
                icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }

        // Loading state on submit
        const form = document.querySelector('form');
        const loginBtn = document.getElementById('loginBtn');
        if (form && loginBtn) {
            form.addEventListener('submit', function () {
                loginBtn.disabled = true;
                loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Đang xác thực...';
            });
        }
    })();
</script>
</x-guest-layout>
