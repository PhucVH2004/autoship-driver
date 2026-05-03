<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page_title', 'Admin') — DeliRoute</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons (dùng ở các trang con: bi-eye, bi-pencil, bi-trash...) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Font Awesome 6 (dùng ở sidebar và dashboard) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 268px;
            --navbar-height: 66px;
            --sidebar-bg: #0f1729;
            --sidebar-hover: rgba(255,255,255,0.06);
            --sidebar-active-bg: rgba(59,130,246,0.18);
            --sidebar-active-border: #3b82f6;
            --sidebar-text: #8b9ab3;
            --sidebar-text-active: #e2e8f0;
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --page-bg: #f0f4f8;
            --card-radius: 14px;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.06);
        }

        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: #1a2540;
            overflow-x: hidden;
            margin: 0;
        }

        /* ── SIDEBAR ── */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .3s ease;
            overflow: hidden;
        }
        #sidebar.closed { transform: translateX(-100%); }

        /* ── MAIN ── */
        #main-wrap {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left .3s ease;
        }

        /* ── NAVBAR ── */
        #top-navbar {
            height: var(--navbar-height);
            background: #fff;
            border-bottom: 1px solid #e5eaf2;
            position: sticky; top: 0;
            z-index: 1030;
            box-shadow: 0 1px 6px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            padding: 0 24px;
            gap: 16px;
        }

        /* ── CONTENT ── */
        #page-content {
            flex: 1;
            padding: 28px 28px 20px;
        }

        /* ── STAT CARDS ── */
        .stat-card {
            border: none;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            transition: transform .22s ease, box-shadow .22s ease;
            overflow: hidden;
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.11);
        }
        .stat-card .card-body { padding: 24px; }
        .stat-card .stat-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .stat-card .stat-val {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }
        .stat-card .stat-lbl {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            margin-top: 2px;
        }
        .stat-card .stat-trend {
            font-size: 0.78rem;
            color: #94a3b8;
            margin-top: 10px;
        }
        .stat-card .trend-up   { color: #10b981; font-weight: 600; }
        .stat-card .trend-down { color: #ef4444; font-weight: 600; }

        /* Decorative circle bên phải card */
        .stat-card::after {
            content: '';
            position: absolute;
            right: -20px; top: -20px;
            width: 110px; height: 110px;
            border-radius: 50%;
            opacity: .07;
            background: currentColor;
        }

        /* ── DATA TABLE ── */
        .data-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        .data-card-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .data-card-header h6 {
            font-weight: 700;
            font-size: .92rem;
            color: #0f172a;
            margin: 0;
        }
        .table thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 2px solid #e9eef5 !important;
            padding: 12px 16px;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 13px 16px;
            vertical-align: middle;
            border-color: #f1f5f9;
            font-size: .875rem;
        }
        .table tbody tr { transition: background .12s; }
        .table tbody tr:hover { background: #fafbff; }

        /* ── STATUS BADGES ── */
        .badge-status {
            padding: 4px 11px;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-status::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: currentColor;
            display: inline-block;
        }
        .s-delivering { background: #dbeafe; color: #2563eb; }
        .s-done       { background: #dcfce7; color: #16a34a; }
        .s-cancelled  { background: #fee2e2; color: #dc2626; }
        .s-pending    { background: #fef9c3; color: #d97706; }

        /* ── BUTTONS ── */
        .btn-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: .84rem;
            border-radius: 9px;
            padding: 9px 18px;
            transition: opacity .18s, transform .15s;
        }
        .btn-gradient:hover { opacity: .88; transform: translateY(-1px); color: #fff; }

        /* ── PAGE HEADING ── */
        .page-title   { font-size: 1.45rem; font-weight: 800; color: #0f172a; }
        .page-sub     { font-size: .83rem; color: #64748b; margin-top: 1px; }

        /* ── CHART CARD ── */
        .chart-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            padding: 22px 24px;
        }
        .chart-card-title {
            font-weight: 700;
            font-size: .92rem;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .chart-card-sub {
            font-size: .78rem;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main-wrap { margin-left: 0; }
            #overlay {
                display: none;
                position: fixed; inset: 0;
                background: rgba(0,0,0,.5);
                z-index: 1035;
            }
            #overlay.show { display: block; }
        }
        @media (min-width: 992px) {
            #overlay { display: none !important; }
        }
    </style>

    @stack('extra_css')
</head>
<body>

<div id="overlay" onclick="closeSidebar()"></div>

{{-- SIDEBAR --}}
<aside id="sidebar">
    @include('components.sidebar')
</aside>

{{-- MAIN --}}
<div id="main-wrap">
    <header id="top-navbar">
        @include('components.navbar')
    </header>
    <main id="page-content">
        @yield('content')
    </main>
    @include('components.footer')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openSidebar()  {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('overlay').classList.add('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('overlay').classList.remove('show');
    }
</script>
@stack('extra_js')
</body>
</html>