<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Shop Portal — @yield('page_title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    @stack('styles')

    <style>
        :root {
            --shop-primary:   #6C63FF;
            --shop-secondary: #A29BFE;
            --shop-dark:      #1A1D2E;
            --shop-darker:    #13151F;
            --shop-sidebar-w: 260px;
            --shop-nav-h:     64px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #F4F6FA; color: #2D3748; }

        /* Navbar */
        .shop-navbar {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--shop-nav-h);
            background: var(--shop-dark);
            display: flex; align-items: center;
            padding: 0 1.5rem; z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,.4);
        }
        .shop-navbar .nav-brand {
            display: flex; align-items: center; gap: .6rem;
            text-decoration: none; margin-right: auto;
        }
        .shop-navbar .nav-brand .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--shop-primary), var(--shop-secondary));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff;
        }
        .shop-navbar .nav-brand span { font-size: 1.05rem; font-weight: 700; color: #fff; }
        .shop-navbar .nav-brand small { display: block; font-size: .65rem; color: rgba(255,255,255,.5); }
        .nav-actions { display: flex; align-items: center; gap: .75rem; }
        .nav-pill-btn {
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
            color: rgba(255,255,255,.75); border-radius: 20px;
            padding: .35rem .9rem; font-size: .82rem; font-weight: 500;
            display: flex; align-items: center; gap: .4rem;
            text-decoration: none; cursor: pointer; transition: all .2s;
        }
        .nav-pill-btn:hover { background: rgba(255,255,255,.14); color: #fff; }
        .shop-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--shop-primary), var(--shop-secondary));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: .85rem;
        }

        /* Sidebar */
        .shop-sidebar {
            position: fixed; top: var(--shop-nav-h); left: 0;
            width: var(--shop-sidebar-w);
            height: calc(100vh - var(--shop-nav-h));
            background: var(--shop-darker);
            overflow-y: auto; display: flex; flex-direction: column;
            transition: transform .3s; z-index: 900; padding-bottom: 1rem;
        }
        .sidebar-shop-card {
            margin: 1.2rem 1rem;
            background: linear-gradient(135deg, rgba(108,99,255,.15), rgba(162,155,254,.05));
            border: 1px solid rgba(108,99,255,.25);
            border-radius: 12px; padding: 1rem;
            display: flex; align-items: center; gap: .8rem;
        }
        .sidebar-shop-card .shop-avatar-lg {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, var(--shop-primary), var(--shop-secondary));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 1.1rem; flex-shrink: 0;
        }
        .sidebar-shop-card .shop-info .name { font-size: .85rem; font-weight: 600; color: #fff; }
        .sidebar-shop-card .shop-info .role { font-size: .72rem; color: var(--shop-secondary); }
        .sidebar-group-label {
            font-size: .63rem; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; color: rgba(255,255,255,.3);
            padding: 1.2rem 1.35rem .45rem;
        }
        .sidebar-nav-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .65rem 1.2rem; margin: .12rem .75rem;
            border-radius: 10px; color: rgba(255,255,255,.55);
            text-decoration: none; font-size: .88rem; font-weight: 500;
            transition: all .2s; position: relative;
        }
        .sidebar-nav-link .nav-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: rgba(255,255,255,.05);
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; flex-shrink: 0; transition: all .2s;
        }
        .sidebar-nav-link:hover { background: rgba(255,255,255,.07); color: #fff; }
        .sidebar-nav-link.active {
            background: linear-gradient(90deg, rgba(108,99,255,.2), rgba(108,99,255,.05));
            color: #fff; border: 1px solid rgba(108,99,255,.2);
        }
        .sidebar-nav-link.active .nav-icon {
            background: linear-gradient(135deg, var(--shop-primary), var(--shop-secondary));
            color: #fff; box-shadow: 0 4px 12px rgba(108,99,255,.4);
        }
        .sidebar-nav-link.active::before {
            content: ''; position: absolute; left: 0; top: 50%;
            transform: translateY(-50%); width: 3px; height: 60%;
            background: var(--shop-primary); border-radius: 0 2px 2px 0;
        }
        .sidebar-divider { height: 1px; background: rgba(255,255,255,.06); margin: .75rem 1.2rem; }

        /* Main */
        .shop-main { margin-left: var(--shop-sidebar-w); margin-top: var(--shop-nav-h); min-height: calc(100vh - var(--shop-nav-h)); display: flex; flex-direction: column; }
        .shop-content { flex: 1; padding: 1.75rem; }
        .shop-footer { background: #fff; border-top: 1px solid #E2E8F0; padding: .9rem 1.75rem; display: flex; align-items: center; justify-content: space-between; font-size: .78rem; color: #A0AEC0; }

        @media (max-width: 991px) {
            .shop-sidebar { transform: translateX(-100%); }
            .shop-sidebar.open { transform: translateX(0); box-shadow: 8px 0 30px rgba(0,0,0,.4); }
            .shop-main { margin-left: 0; }
            .sidebar-toggle { display: block !important; }
            .sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 850; display: none; }
            .sidebar-overlay.show { display: block; }
        }
        @media (min-width: 992px) { .sidebar-toggle { display: none; } }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Navbar -->
<nav class="shop-navbar">
    <button class="nav-pill-btn sidebar-toggle" onclick="toggleSidebar()" style="display:none;">
        <i class="bi bi-list"></i>
    </button>

    <a href="{{ route('shop.dashboard') }}" class="nav-brand">
        <div class="brand-icon"><i class="bi bi-shop"></i></div>
        <div>
            <span>DeliRoute</span>
            <small>Shop Portal</small>
        </div>
    </a>

    <div class="nav-actions">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-pill-btn">
                <i class="bi bi-box-arrow-right"></i> Đăng xuất
            </button>
        </form>
        <div class="shop-avatar">
            {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
        </div>
    </div>
</nav>

<!-- Sidebar -->
<aside class="shop-sidebar" id="shopSidebar">
    <div class="sidebar-shop-card">
        <div class="shop-avatar-lg">
            {{ strtoupper(substr(Auth::user()->shop?->ten_shop ?? Auth::user()->name ?? 'S', 0, 1)) }}
        </div>
        <div class="shop-info">
            <div class="name">{{ Auth::user()->shop?->ten_shop ?? Auth::user()->name }}</div>
            <div class="role">Shop Partner</div>
        </div>
    </div>

    <div class="sidebar-group-label">Chính</div>

    <a href="{{ route('shop.dashboard') }}"
       class="sidebar-nav-link {{ request()->routeIs('shop.dashboard') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-speedometer2"></i></span>
        Dashboard
    </a>

    <a href="{{ route('shop.don_hang.index') }}"
       class="sidebar-nav-link {{ request()->routeIs('shop.don_hang.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-box-seam"></i></span>
        Đơn hàng
    </a>

    <a href="{{ route('shop.tai_chinh.index') }}"
       class="sidebar-nav-link {{ request()->routeIs('shop.tai_chinh.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi bi-wallet2"></i></span>
        Tài chính
    </a>

    <div class="sidebar-divider"></div>

    <div class="sidebar-group-label">Tài khoản</div>
    <form method="POST" action="{{ route('logout') }}" id="logoutSidebarForm">
        @csrf
        <a href="#" class="sidebar-nav-link"
           onclick="event.preventDefault(); document.getElementById('logoutSidebarForm').submit();">
            <span class="nav-icon"><i class="bi bi-power"></i></span>
            Đăng xuất
        </a>
    </form>
</aside>

<!-- Content -->
<main class="shop-main">
    <div class="shop-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 fade show">
                <i class="bi bi-check-circle-fill"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 fade show">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="shop-footer">
        <span>© {{ date('Y') }} <strong style="color:var(--shop-primary)">DeliRoute</strong> — Shop Portal</span>
        <span>v1.0</span>
    </footer>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('shopSidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('shopSidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            new bootstrap.Alert(a);
            setTimeout(() => a.classList.remove('show'), 3800);
        });
    }, 200);
</script>
@stack('extra_js')
@stack('scripts')
</body>
</html>
