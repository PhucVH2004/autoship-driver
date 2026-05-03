{{-- components/sidebar.blade.php --}}
<style>
    /* ── LOGO ── */
    .sb-logo {
        padding: 22px 22px 18px;
        display: flex;
        align-items: center;
        gap: 13px;
        border-bottom: 1px solid rgba(255,255,255,.07);
        text-decoration: none;
    }
    .sb-logo-icon {
        width: 42px; height: 42px;
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(59,130,246,.45);
    }
    .sb-logo-name  { font-size: .97rem; font-weight: 700; color: #f1f5f9; letter-spacing: .02em; }
    .sb-logo-sub   { font-size: .68rem; color: #475569; font-weight: 400; }

    /* ── SECTION LABEL ── */
    .sb-section {
        padding: 18px 22px 5px;
        font-size: .62rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: #334155;
    }

    /* ── MENU ITEM ── */
    .sb-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 1px 10px;
        padding: 10px 14px;
        border-radius: 9px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: .855rem;
        font-weight: 500;
        transition: background .15s, color .15s;
        position: relative;
    }
    .sb-item:hover {
        background: var(--sidebar-hover);
        color: #cbd5e1;
    }
    .sb-item.active {
        background: var(--sidebar-active-bg);
        color: var(--sidebar-text-active);
        font-weight: 600;
    }
    /* Gạch dọc khi active */
    .sb-item.active::before {
        content: '';
        position: absolute;
        left: -10px; top: 8px; bottom: 8px;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: var(--sidebar-active-border);
    }
    .sb-item .sb-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: .88rem;
        background: rgba(255,255,255,.05);
        flex-shrink: 0;
        transition: background .15s;
    }
    .sb-item.active .sb-icon  { background: rgba(59,130,246,.25); color: #60a5fa; }
    .sb-item:hover  .sb-icon  { background: rgba(255,255,255,.08); }

    .sb-badge {
        margin-left: auto;
        background: #ef4444;
        color: #fff;
        font-size: .64rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 10px;
        animation: pulse-badge 2s infinite;
    }
    @keyframes pulse-badge {
        0%,100% { opacity: 1; }
        50%      { opacity: .65; }
    }

    /* ── DRIVER STATUS ROW ── */
    .sb-driver-row {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 6px 22px;
    }
    .sb-driver-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .sb-driver-name { font-size: .75rem; color: #64748b; }

    /* ── FOOTER ── */
    .sb-footer {
        margin-top: auto;
        padding: 14px 22px;
        border-top: 1px solid rgba(255,255,255,.06);
        font-size: .72rem;
        color: #334155;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .sb-footer .dot-online {
        width: 7px; height: 7px;
        background: #22c55e;
        border-radius: 50%;
        box-shadow: 0 0 6px #22c55e;
    }
</style>

{{-- LOGO --}}
<a href="{{ route('admin.dashboard') }}" class="sb-logo">
    <div class="sb-logo-icon"><i class="fa-solid fa-truck-fast"></i></div>
    <div>
        <div class="sb-logo-name">DeliRoute</div>
        <div class="sb-logo-sub">Logistics Management</div>
    </div>
</a>

{{-- MENU --}}
<nav style="flex:1; overflow-y:auto; padding-bottom:8px;">

    <div class="sb-section">Tổng quan</div>

    <a href="{{ route('admin.dashboard') }}"
       class="sb-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-gauge-high"></i></span>
        Dashboard
    </a>

    <div class="sb-section">Quản lý</div>

    <a href="{{ route('admin.don_hang.index') }}"
       class="sb-item {{ request()->routeIs('admin.don_hang.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-box-open"></i></span>
        Đơn hàng
        <span class="sb-badge">12</span>
    </a>

    <a href="{{ route('admin.tai_xe.index') }}"
       class="sb-item {{ request()->routeIs('admin.tai_xe.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-id-card"></i></span>
        Tài xế
    </a>

    <a href="{{ route('admin.khach_hang.index') }}"
       class="sb-item {{ request()->routeIs('admin.khach_hang.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-users"></i></span>
        Khách hàng
    </a>

    <div class="sb-section">Vận chuyển</div>

    <a href="{{ route('admin.map.index') }}"
       class="sb-item {{ request()->routeIs('admin.map.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-map-location-dot"></i></span>
        Bản đồ giao hàng
    </a>

    <a href="{{ route('admin.lo_trinh.index') }}"
       class="sb-item {{ request()->routeIs('admin.lo_trinh.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-route"></i></span>
        Lộ trình tài xế
    </a>

    <div class="sb-section">Hệ thống</div>

    <a href="{{ route('admin.users.index') }}"
       class="sb-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-user-gear"></i></span>
        Người dùng
    </a>

    <a href="{{ route('admin.roles.index') }}"
       class="sb-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-shield-halved"></i></span>
        Phân quyền
    </a>

    <a href="{{ route('admin.system_fees.edit') }}"
       class="sb-item {{ request()->routeIs('admin.system_fees.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-money-bill-wave"></i></span>
        Phí hệ thống
    </a>

    <a href="{{ route('admin.settlement.index') }}"
       class="sb-item {{ request()->routeIs('admin.settlement.*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fa-solid fa-scale-balanced"></i></span>
        Đối soát tài chính
    </a>

</nav>

{{-- FOOTER --}}
<div class="sb-footer">
    <span class="dot-online"></span>
    Hệ thống hoạt động tốt &bull; v1.0
</div>
