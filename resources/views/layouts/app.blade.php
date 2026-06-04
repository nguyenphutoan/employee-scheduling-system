<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Scheduler')</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-width-collapsed: 80px;
            --sidebar-bg: #0D6EFD;
        }

        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            min-height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            transition: all 0.3s ease;
            overflow-x: hidden;
            white-space: nowrap;
            z-index: 1050;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
        }

        .sidebar.collapsed { width: var(--sidebar-width-collapsed) !important; }
        .sidebar.collapsed .brand-link { display: none !important; }
        .sidebar.collapsed .header-bar { justify-content: center !important; padding-left: 0 !important; }
        .sidebar.collapsed #sidebarToggle i::before { content: "\f479"; font-size: 1.8rem; }
        .sidebar.collapsed .link-text, .sidebar.collapsed .user-name { display: none; opacity: 0; transition: opacity 0.3s; }
        .sidebar.collapsed .nav-link { text-align: center; padding-left: 0; padding-right: 0; justify-content: center; }
        .sidebar.collapsed .nav-link i { margin-right: 0 !important; font-size: 1.2rem; }
        .sidebar.collapsed .collapsed-dot { display: block !important; }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            padding: 12px 15px;
            margin-bottom: 5px; 
            transition: all 0.2s ease-in-out; 
            border-radius: 8px;
        }

        .nav-link:hover { color: #fff; background-color: rgba(255, 255, 255, 0.15); transform: translateX(5px); }
        .nav-link.active { color: var(--sidebar-bg) !important; background-color: #fff !important; font-weight: bold; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .nav-link.active i { color: var(--sidebar-bg) !important; }

        #sidebarToggle { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; padding: 5px; display: flex; align-items: center; justify-content: center; }

        /* --- TỐI ƯU HÓA MOBILE MENU --- */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -300px;
                /* Sử dụng dvh để không bị lỗi chiều cao bởi thanh địa chỉ trình duyệt */
                height: 100dvh; 
                min-height: 100dvh;
                transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .sidebar.show-mobile { left: 0; width: 280px !important; }

            #mobile-overlay {
                display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100dvh; background: rgba(0,0,0,0.5); z-index: 1040; opacity: 0; transition: opacity 0.3s;
            }

            #mobile-overlay.show { display: block; opacity: 1; }
            #sidebarToggle { display: none; }
        }

        /* Tùy chỉnh thanh cuộn cho khu vực nội dung chính */
        main::-webkit-scrollbar { width: 6px; }
        main::-webkit-scrollbar-track { background: transparent; }
        main::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
        main::-webkit-scrollbar-thumb:hover { background: #aaa; }
    </style>
</head>
<body>
    <div id="mobile-overlay" onclick="toggleMobileMenu()"></div>

    <div id="app" class="d-flex">
        
        @auth
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white sidebar" id="sidebar">
            
            <div class="header-bar d-flex align-items-center justify-content-between mb-4 mb-md-0 me-md-auto text-white text-decoration-none w-100">
                <a href="/" class="d-flex align-items-center text-white text-decoration-none brand-link">
                    <div class="bg-white text-primary rounded d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 35px; height: 35px;">
                        <i class="bi bi-clock-fill fs-5"></i>
                    </div>
                    <span class="fs-4 fw-bold brand-text">Scheduler</span>
                </a>

                {{-- Nút thu gọn trên PC --}}
                <button id="sidebarToggle" class="d-none d-md-flex">
                    <i class="bi bi-chevron-left"></i>
                </button>
                
                {{-- Nút đóng Menu trên Mobile --}}
                <button class="btn btn-link text-white d-md-none p-0" onclick="toggleMobileMenu()">
                    <i class="bi bi-x-lg fs-3"></i>
                </button>
            </div>

            <hr class="border-light opacity-25">
            
            <ul class="nav nav-pills flex-column mb-auto gap-1">
                @if(Auth::user()->Role == 'Manager')
                    <li class="nav-item">
                        <a href="{{ route('manager.dashboard') }}" class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-table me-3 fs-5"></i> <span class="link-text">Bảng lịch tuần</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.scheduling') }}" class="nav-link {{ request()->routeIs('manager.scheduling') ? 'active' : '' }}">
                            <i class="bi bi-calendar-week me-3 fs-5"></i> <span class="link-text">Bảng xếp lịch</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.employees') }}" class="nav-link {{ request()->routeIs('manager.employees') ? 'active' : '' }}">
                            <i class="bi bi-people me-3 fs-5"></i> <span class="link-text">Quản lý nhân viên</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.payroll') }}" class="nav-link {{ request()->routeIs('manager.payroll') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin me-3 fs-5"></i> <span class="link-text">Bảng lương</span>
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }} position-relative">
                            <i class="bi bi-chat-dots-fill me-3 fs-5"></i> <span class="link-text">Nhắn tin nội bộ</span>
                            @if(isset($globalUnreadMsgCount) && $globalUnreadMsgCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto link-text shadow-sm" style="font-size: 0.75rem;">
                                    {{ $globalUnreadMsgCount > 99 ? '99+' : $globalUnreadMsgCount }}
                                </span>
                                <span class="position-absolute bg-danger border border-light border-2 rounded-circle collapsed-dot" style="top: 12px; right: 12px; width: 12px; height: 12px; display: none;"></span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.profile') }}" class="nav-link {{ request()->routeIs('manager.profile') ? 'active' : '' }}">
                            <i class="bi bi-person-circle me-3 fs-5"></i> <span class="link-text">Hồ sơ</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check me-3 fs-5"></i> <span class="link-text">Lịch làm của tôi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.register') }}" class="nav-link {{ request()->routeIs('staff.register') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square me-3 fs-5"></i> <span class="link-text">Đăng ký lịch</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('staff.payroll') }}" class="nav-link {{ request()->routeIs('staff.payroll') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin me-3 fs-5"></i> <span class="link-text">Bảng lương</span>
                        </a>
                    </li>
                    <li class="nav-item mt-2">
                        <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }} position-relative">
                            <i class="bi bi-chat-dots-fill me-3 fs-5"></i> <span class="link-text">Nhắn tin nội bộ</span>
                            @if(isset($globalUnreadMsgCount) && $globalUnreadMsgCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto link-text shadow-sm" style="font-size: 0.75rem;">
                                    {{ $globalUnreadMsgCount > 99 ? '99+' : $globalUnreadMsgCount }}
                                </span>
                                <span class="position-absolute bg-danger border border-light border-2 rounded-circle collapsed-dot" style="top: 12px; right: 12px; width: 12px; height: 12px; display: none;"></span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('staff.profile') }}" class="nav-link {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
                            <i class="bi bi-person-circle me-3 fs-5"></i> <span class="link-text">Hồ sơ cá nhân</span>
                        </a>
                    </li>
                @endif
            </ul>
            
            <hr class="border-light opacity-25">
            
            <div class="dropdown mt-2">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle justify-content-center-collapsed p-2 rounded hover-bg-light" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false" style="transition: background 0.3s;">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 35px; height: 35px;">
                        {{ substr(Auth::user()->FullName, 0, 1) }}
                    </div>
                    <strong class="user-name text-truncate" style="max-width: 150px;">{{ Auth::user()->FullName }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow border-0" aria-labelledby="dropdownUser1">
                    <li class="nav-item mt-auto">
                        <a href="#" class="nav-link text-white px-3" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        @endauth

        {{-- Sửa vh thành dvh để fix lỗi thanh cuộn trên điện thoại --}}
        <main class="flex-grow-1" style="height: 100dvh; max-height: 100dvh; overflow-y: auto; overflow-x: hidden;">
            
            {{-- THANH ĐIỀU HƯỚNG TRÊN MOBILE CHUẨN APP --}}
            <nav class="navbar navbar-light bg-white shadow-sm d-md-none sticky-top border-bottom">
                <div class="container-fluid px-3 py-1">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link text-dark p-0 me-3" onclick="toggleMobileMenu()">
                            <i class="bi bi-list fs-1"></i>
                        </button>
                        <span class="navbar-brand mb-0 h4 fw-bold text-primary">Scheduler</span>
                    </div>
                    
                    @auth
                    <div class="d-flex align-items-center gap-3">
                        {{-- ICON CHAT HIỂN THỊ TRỰC TIẾP TRÊN NAVBAR ĐIỆN THOẠI --}}
                        <a href="{{ route('chat.index') }}" class="position-relative text-dark text-decoration-none">
                            <i class="bi bi-chat-dots fs-3"></i>
                            @if(isset($globalUnreadMsgCount) && $globalUnreadMsgCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white" style="font-size: 0.6rem;">
                                    {{ $globalUnreadMsgCount > 99 ? '99+' : $globalUnreadMsgCount }}
                                </span>
                            @endif
                        </a>
                        
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; font-size: 1rem; font-weight: bold;">
                            {{ substr(Auth::user()->FullName, 0, 1) }}
                        </div>
                    </div>
                    @endauth
                </div>
            </nav>

            <div class="p-3 p-md-4 pb-5 pb-md-4">
                @if(Auth::check() && Auth::user()->EndDate)
                    <div class="alert alert-warning text-center rounded-3 shadow-sm fw-bold mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i> 
                        Tài khoản này đã ngưng hoạt động. Bạn chỉ có quyền xem dữ liệu.
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        function toggleMobileMenu() {
            document.getElementById('sidebar').classList.toggle('show-mobile');
            document.getElementById('mobile-overlay').classList.toggle('show');
        }

        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const STORAGE_KEY = 'sidebarState';

            const savedState = localStorage.getItem(STORAGE_KEY);
            if (savedState === 'collapsed') {
                sidebar.classList.add('collapsed');
            }

            if(toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('collapsed');
                    
                    if (sidebar.classList.contains('collapsed')) {
                        localStorage.setItem(STORAGE_KEY, 'collapsed');
                    } else {
                        localStorage.setItem(STORAGE_KEY, 'expanded');
                    }
                });
            }
        });
    </script>
</body>
</html>