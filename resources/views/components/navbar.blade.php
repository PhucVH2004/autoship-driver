{{--
    NAVBAR COMPONENT (components/navbar.blade.php)
    - Nút hamburger để mở sidebar trên mobile
    - Thanh tìm kiếm
    - Icon thông báo
    - Avatar người dùng + dropdown
--}}
<div class="d-flex align-items-center h-100 px-4 gap-3">

    {{-- Nút mở sidebar (chỉ hiện trên mobile) --}}
    <button class="btn btn-sm btn-light d-lg-none me-2 rounded-2" onclick="openSidebar()" title="Mở menu">
        <i class="bi bi-list fs-5"></i>
    </button>

    {{-- Tiêu đề / breadcrumb --}}
    <div class="d-none d-md-block">
        <span class="fw-600 text-dark" style="font-weight:600; font-size:0.95rem;">
            @yield('page_title', 'Dashboard')
        </span>
    </div>

    {{-- Đẩy các icon sang phải --}}
    <div class="ms-auto d-flex align-items-center gap-2">

        {{-- Thanh tìm kiếm (ẩn trên màn hình nhỏ) --}}
        <div class="input-group d-none d-md-flex" style="width:220px;">
            <span class="input-group-text bg-light border-0 rounded-start-3 text-muted">
                <i class="bi bi-search fs-6"></i>
            </span>
            <input
                type="text"
                id="search-input"
                class="form-control bg-light border-0 rounded-end-3"
                placeholder="Tìm kiếm..."
                style="font-size:0.88rem;"
            >
        </div>

        {{-- Chuông thông báo --}}
        <div class="dropdown">
            <button
                class="btn btn-light rounded-circle p-2 position-relative"
                id="btn-notifications"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="Thông báo"
            >
                <i class="bi bi-bell fs-5"></i>
                {{-- Chấm đỏ cho biết có thông báo mới --}}
                <span class="position-absolute top-1 start-75 translate-middle badge rounded-pill bg-danger"
                    style="font-size:0.6rem; padding:3px 5px;">3</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="min-width:300px; border-radius:12px;">
                <li class="px-3 py-2 border-bottom">
                    <span class="fw-bold text-dark" style="font-size:0.9rem;">Thông báo</span>
                </li>
                {{-- Danh sách thông báo mẫu --}}
                @php
                    $notifications = [
                        ['icon' => 'bi-box',        'color' => 'text-primary', 'text' => 'Đơn hàng #DH001 vừa được tạo', 'time' => '5 phút trước'],
                        ['icon' => 'bi-truck',       'color' => 'text-success', 'text' => 'Tài xế Nguyễn Văn A đang giao hàng', 'time' => '12 phút trước'],
                        ['icon' => 'bi-exclamation-triangle', 'color' => 'text-warning', 'text' => 'Đơn hàng #DH005 bị hoãn', 'time' => '30 phút trước'],
                    ];
                @endphp
                @foreach ($notifications as $notification)
                <li>
                    <a class="dropdown-item py-2 d-flex gap-3 align-items-start" href="#">
                        <i class="bi {{ $notification['icon'] }} {{ $notification['color'] }} mt-1"></i>
                        <div>
                            <div style="font-size:0.85rem;">{{ $notification['text'] }}</div>
                            <small class="text-muted">{{ $notification['time'] }}</small>
                        </div>
                    </a>
                </li>
                @endforeach
                <li class="border-top">
                    <a class="dropdown-item text-center py-2 text-primary" href="#" style="font-size:0.85rem;">
                        Xem tất cả thông báo
                    </a>
                </li>
            </ul>
        </div>

        {{-- Avatar + dropdown tài khoản --}}
        <div class="dropdown">
            <button
                class="btn d-flex align-items-center gap-2 rounded-pill px-3 py-1 border"
                id="btn-user-menu"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                style="background:#f8fafc; font-size:0.88rem;"
            >
                {{-- Avatar chữ cái đầu --}}
                <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                    style="width:32px; height:32px; font-size:0.8rem;">
                    @auth{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}@else A @endauth
                </span>
                <span class="d-none d-md-inline fw-500">
                    @auth{{ auth()->user()->name }}@else Admin @endauth
                </span>
                <i class="bi bi-chevron-down" style="font-size:0.7rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius:12px; min-width:200px;">
                <li>
                    <a class="dropdown-item py-2" href="#" style="font-size:0.88rem;">
                        <i class="bi bi-person me-2 text-muted"></i> Hồ sơ cá nhân
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="#" style="font-size:0.88rem;">
                        <i class="bi bi-gear me-2 text-muted"></i> Cài đặt
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger" style="font-size:0.88rem;">
                            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                        </button>
                    </form>
                    @else
                    <a class="dropdown-item py-2 text-danger" href="#" style="font-size:0.88rem;">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                    </a>
                    @endauth
                </li>
            </ul>
        </div>

    </div>
</div>
