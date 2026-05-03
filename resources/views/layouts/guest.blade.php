<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'GiaoHang Pro') }} — @yield('page_title', 'Đăng nhập')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* ── Global ────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
        }

        /* Animated background blobs */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: float 8s ease-in-out infinite alternate;
            pointer-events: none;
        }
        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #6c63ff, #3b82f6);
            top: -100px; left: -100px;
        }
        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #f472b6, #a855f7);
            bottom: -80px; right: -80px;
            animation-delay: -4s;
        }

        @keyframes float {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(40px, 30px) scale(1.1); }
        }

        /* ── Auth Card ─────────────────────────────────────────── */
        .auth-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 2.5rem 2.25rem;
            box-shadow:
                0 25px 50px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255,255,255,0.12);
            animation: slideUp 0.5s ease-out both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Brand / Logo ──────────────────────────────────────── */
        .brand-logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #6c63ff, #3b82f6);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(108,99,255,0.45);
        }
        .brand-logo i { font-size: 1.7rem; color: #fff; }

        .brand-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            text-align: center;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.55);
            text-align: center;
            margin-bottom: 2rem;
        }

        /* ── Form Label ─────────────────────────────────────────── */
        .form-label-custom {
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 0.4rem;
        }

        /* ── Input Group ─────────────────────────────────────────── */
        .input-group-custom {
            position: relative;
            margin-bottom: 1.25rem;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.4);
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s;
            z-index: 5;
        }
        .form-control-custom {
            width: 100%;
            background: rgba(255,255,255,0.08);
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 12px;
            padding: 0.75rem 1rem 0.75rem 2.65rem;
            color: #fff;
            font-size: 0.92rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.25s;
            outline: none;
        }
        .form-control-custom::placeholder { color: rgba(255,255,255,0.3); }
        .form-control-custom:focus {
            background: rgba(255,255,255,0.13);
            border-color: #6c63ff;
            box-shadow: 0 0 0 4px rgba(108,99,255,0.2);
            color: #fff;
        }
        .form-control-custom:focus + .input-icon,
        .input-group-custom:focus-within .input-icon { color: #a78bfa; }

        /* password toggle button */
        .toggle-pass {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: rgba(255,255,255,0.35);
            cursor: pointer; font-size: 1rem;
            transition: color 0.2s;
            z-index: 5;
        }
        .toggle-pass:hover { color: #a78bfa; }

        /* ── Checkbox ────────────────────────────────────────────── */
        .custom-check {
            display: flex; align-items: center; gap: 0.5rem;
            cursor: pointer;
        }
        .custom-check input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: #6c63ff;
            cursor: pointer;
        }
        .custom-check span {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
            user-select: none;
        }

        /* ── Primary Button ──────────────────────────────────────── */
        .btn-auth {
            width: 100%;
            padding: 0.82rem;
            font-size: 0.95rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.3px;
            color: #fff;
            background: linear-gradient(135deg, #6c63ff 0%, #3b82f6 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s;
            box-shadow: 0 6px 20px rgba(108,99,255,0.4);
            position: relative;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .btn-auth::after {
            content: '';
            position: absolute; inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.2s;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(108,99,255,0.55);
        }
        .btn-auth:hover::after { background: rgba(255,255,255,0.06); }
        .btn-auth:active { transform: translateY(0); }

        /* ── Divider ─────────────────────────────────────────────── */
        .divider {
            display: flex; align-items: center; gap: 0.75rem;
            margin: 1.5rem 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.12);
        }
        .divider span {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.4);
            white-space: nowrap;
        }

        /* ── Link footer ─────────────────────────────────────────── */
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.87rem;
            color: rgba(255,255,255,0.5);
        }
        .auth-footer a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .auth-footer a:hover { color: #c4b5fd; }

        /* ── Forgot link ─────────────────────────────────────────── */
        .forgot-link {
            font-size: 0.82rem;
            color: #a78bfa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .forgot-link:hover { color: #c4b5fd; }

        /* ── Alert / Error ───────────────────────────────────────── */
        .alert-custom {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.35);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            color: #fca5a5;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }
        .alert-success-custom {
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.35);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            color: #86efac;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
        }

        /* field-level errors */
        .field-error {
            font-size: 0.78rem;
            color: #f87171;
            margin-top: 0.3rem;
            padding-left: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        {{ $slot }}
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(btnId, inputId) {
            const btn   = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            if (!btn || !input) return;
            btn.addEventListener('click', () => {
                const isPass = input.type === 'password';
                input.type   = isPass ? 'text' : 'password';
                btn.querySelector('i').className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
    </script>
</body>
</html>
