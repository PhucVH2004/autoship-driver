<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DeliRoute Driver — @yield('page_title', 'Tài Xế')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Leaflet CSS (cho trang bản đồ) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    @stack('styles')

    <style>
        :root {
            --driver-primary:    #FF6B2B;
            --driver-secondary:  #FF9A5C;
            --driver-dark:       #1A1D2E;
            --driver-darker:     #13151F;
            --driver-sidebar-w:  260px;
            --driver-nav-h:      64px;
            --driver-accent:     #4ECDC4;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #F4F6FA;
            color: #2D3748;
        }

        /* ── TOP NAVBAR ─────────────────────────── */
        .driver-navbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--driver-nav-h);
            background: var(--driver-dark);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,.4);
        }

        .driver-navbar .nav-brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            text-decoration: none;
            margin-right: auto;
        }

        .driver-navbar .nav-brand .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--driver-primary), var(--driver-secondary));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            color: #fff;
        }

        .driver-navbar .nav-brand span {
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -.3px;
        }

        .driver-navbar .nav-brand small {
            display: block;
            font-size: .65rem;
            font-weight: 400;
            color: rgba(255,255,255,.5);
            margin-top: -3px;
        }

        .driver-navbar .nav-actions {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .driver-navbar .sidebar-toggle {
            background: none;
            border: none;
            color: rgba(255,255,255,.7);
            font-size: 1.3rem;
            cursor: pointer;
            padding: .4rem;
            border-radius: 8px;
            display: none;
            transition: all .2s;
        }

        .driver-navbar .sidebar-toggle:hover { color: #fff; background: rgba(255,255,255,.1); }

        .nav-pill-btn {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1);
            color: rgba(255,255,255,.75);
            border-radius: 20px;
            padding: .35rem .9rem;
            font-size: .82rem;
            font-weight: 500;
            display: flex; align-items: center; gap: .4rem;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
        }
        .nav-pill-btn:hover { background: rgba(255,255,255,.14); color: #fff; }

        .driver-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--driver-primary), var(--driver-secondary));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            cursor: pointer;
        }

        .status-dot {
            width: 8px; height: 8px;
            background: #22d3a0;
            border-radius: 50%;
            display: inline-block;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50%       { opacity: .4; }
        }

        /* ── SIDEBAR ────────────────────────────── */
        .driver-sidebar {
            position: fixed;
            top: var(--driver-nav-h);
            left: 0;
            width: var(--driver-sidebar-w);
            height: calc(100vh - var(--driver-nav-h));
            background: var(--driver-darker);
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            z-index: 900;
            padding-bottom: 1rem;
        }

        .driver-sidebar::-webkit-scrollbar { width: 3px; }
        .driver-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 2px; }

        /* Driver info card at top of sidebar */
        .sidebar-driver-card {
            margin: 1.2rem 1rem;
            background: linear-gradient(135deg, rgba(255,107,43,.15), rgba(255,154,92,.05));
            border: 1px solid rgba(255,107,43,.25);
            border-radius: 12px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .sidebar-driver-card .driver-avatar-lg {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--driver-primary), var(--driver-secondary));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .sidebar-driver-card .driver-info .name {
            font-size: .85rem;
            font-weight: 600;
            color: #fff;
        }

        .sidebar-driver-card .driver-info .role {
            font-size: .72rem;
            color: var(--driver-secondary);
            font-weight: 500;
        }

        .sidebar-driver-card .driver-info .status-badge {
            font-size: .68rem;
            background: rgba(34,211,160,.15);
            color: #22d3a0;
            border: 1px solid rgba(34,211,160,.3);
            border-radius: 20px;
            padding: .12rem .55rem;
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            margin-top: .25rem;
        }

        /* Nav group label */
        .sidebar-group-label {
            font-size: .63rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: rgba(255,255,255,.3);
            padding: 1.2rem 1.35rem .45rem;
        }

        /* Nav link */
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .65rem 1.2rem;
            margin: .12rem .75rem;
            border-radius: 10px;
            color: rgba(255,255,255,.55);
            text-decoration: none;
            font-size: .88rem;
            font-weight: 500;
            transition: all .2s;
            position: relative;
        }

        .sidebar-nav-link .nav-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: rgba(255,255,255,.05);
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
            transition: all .2s;
        }

        .sidebar-nav-link:hover {
            background: rgba(255,255,255,.07);
            color: #fff;
        }

        .sidebar-nav-link:hover .nav-icon {
            background: rgba(255,107,43,.2);
            color: var(--driver-secondary);
        }

        .sidebar-nav-link.active {
            background: linear-gradient(90deg, rgba(255,107,43,.2), rgba(255,107,43,.05));
            color: #fff;
            border: 1px solid rgba(255,107,43,.2);
        }

        .sidebar-nav-link.active .nav-icon {
            background: linear-gradient(135deg, var(--driver-primary), var(--driver-secondary));
            color: #fff;
            box-shadow: 0 4px 12px rgba(255,107,43,.4);
        }

        .sidebar-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%;
            background: var(--driver-primary);
            border-radius: 0 2px 2px 0;
        }

        .sidebar-nav-link .badge-count {
            margin-left: auto;
            background: var(--driver-primary);
            color: #fff;
            font-size: .65rem;
            font-weight: 700;
            border-radius: 20px;
            padding: .15rem .5rem;
            min-width: 20px;
            text-align: center;
        }

        /* Sidebar divider */
        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,.06);
            margin: .75rem 1.2rem;
        }

        /* ── MAIN CONTENT ───────────────────────── */
        .driver-main {
            margin-left: var(--driver-sidebar-w);
            margin-top: var(--driver-nav-h);
            min-height: calc(100vh - var(--driver-nav-h));
            display: flex;
            flex-direction: column;
        }

        .driver-content {
            flex: 1;
            padding: 1.75rem;
        }

        /* ── PAGE HEADER ────────────────────────── */
        .page-header {
            margin-bottom: 1.75rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-header .page-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1A202C;
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .page-header .page-title .title-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            color: #fff;
        }

        .page-header .page-subtitle {
            color: #718096;
            font-size: .88rem;
            margin-top: .25rem;
        }

        /* ── DRIVER FOOTER ──────────────────────── */
        .driver-footer {
            background: #fff;
            border-top: 1px solid #E2E8F0;
            padding: .9rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: .78rem;
            color: #A0AEC0;
        }

        .driver-footer .footer-brand {
            color: var(--driver-primary);
            font-weight: 600;
        }

        /* ── RESPONSIVE ─────────────────────────── */
        @media (max-width: 991px) {
            .driver-navbar .sidebar-toggle { display: block; margin-right: .5rem; }
            .driver-sidebar {
                transform: translateX(-100%);
            }
            .driver-sidebar.open {
                transform: translateX(0);
                box-shadow: 8px 0 30px rgba(0,0,0,.4);
            }
            .driver-main {
                margin-left: 0;
            }
            .sidebar-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 850;
                display: none;
            }
            .sidebar-overlay.show { display: block; }
        }

        @media (max-width: 576px) {
            .driver-content { padding: 1rem; }
            .page-header { flex-direction: column; }
        }
    </style>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ── TOP NAVBAR ──────────────────────────────────────────── -->
<nav class="driver-navbar">
    <button class="sidebar-toggle" onclick="toggleSidebar()" id="sidebarToggle" title="Menu">
        <i class="bi bi-list"></i>
    </button>

    <a href="{{ route('driver.dashboard') }}" class="nav-brand">
        <div class="brand-icon"><i class="bi bi-truck"></i></div>
        <div>
            <span>DeliRoute</span>
            <small>Driver Portal</small>
        </div>
    </a>

    <div class="nav-actions">
        <!-- Status indicator -->
        <div class="nav-pill-btn">
            <span class="status-dot"></span>
            Đang hoạt động
        </div>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-pill-btn">
                <i class="bi bi-box-arrow-right"></i>
                Đăng xuất
            </button>
        </form>

        <!-- Avatar -->
        <div class="driver-avatar" title="{{ Auth::user()->name ?? 'Tài Xế' }}">
            {{ strtoupper(substr(Auth::user()->name ?? 'T', 0, 1)) }}
        </div>
    </div>
</nav>

<!-- ── SIDEBAR ──────────────────────────────────────────────── -->
<aside class="driver-sidebar" id="driverSidebar">

    <!-- Driver Info Card -->
    <div class="sidebar-driver-card">
        <div class="driver-avatar-lg">
            {{ strtoupper(substr(Auth::user()->name ?? 'T', 0, 1)) }}
        </div>
        <div class="driver-info">
            <div class="name">{{ Auth::user()->name ?? 'Tài Xế' }}</div>
            <div class="role">Tài xế giao hàng</div>
            <div class="status-badge">
                <span class="status-dot" style="width:6px;height:6px;"></span>
                Sẵn sàng
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="sidebar-group-label">Chính</div>

    <a href="{{ route('driver.dashboard') }}"
       class="sidebar-nav-link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
        Dashboard
    </a>

    <a href="{{ route('driver.orders') }}"
       class="sidebar-nav-link {{ request()->routeIs('driver.orders*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-bag-check"></i></span>
        Đơn hàng của tôi
        {{-- <span class="badge-count">3</span> --}}
    </a>

    <a href="{{ route('driver.route') }}"
       class="sidebar-nav-link {{ request()->routeIs('driver.route*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-map"></i></span>
        Lộ trình hôm nay
    </a>

    <a href="{{ route('driver.wallet.index') }}"
       class="sidebar-nav-link {{ request()->routeIs('driver.wallet.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
        Ví của tôi
    </a>

    <div class="sidebar-divider"></div>

    <!-- Account section -->
    <div class="sidebar-group-label">Tài khoản</div>

    <form method="POST" action="{{ route('logout') }}" id="logoutSidebarForm">
        @csrf
        <a href="#" class="sidebar-nav-link" onclick="event.preventDefault(); document.getElementById('logoutSidebarForm').submit();">
            <span class="nav-icon"><i class="bi bi-power"></i></span>
            Đăng xuất
        </a>
    </form>
</aside>

<!-- ── MAIN CONTENT ─────────────────────────────────────────── -->
<main class="driver-main">
    <div class="driver-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 shadow-sm mb-4 fade show" role="alert">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 shadow-sm mb-4 fade show" role="alert">
                <i class="bi bi-exclamation-circle-fill text-danger"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="driver-footer">
        <span>© {{ date('Y') }} <span class="footer-brand">DeliRoute</span> — Driver Portal</span>
        <span>v1.0 · <span style="color:#22d3a0;">●</span> Online</span>
    </footer>
</main>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('driverSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
    }

    function closeSidebar() {
        document.getElementById('driverSidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }

    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            if (bootstrap.Alert.getInstance(a)) return;
            new bootstrap.Alert(a);
            a.classList.remove('show');
        });
    }, 4000);
</script>

@stack('scripts')
</body>
</html>
