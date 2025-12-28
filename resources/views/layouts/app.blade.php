<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        .sidebar {
            min-height: 100vh;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            transition: all 0.3s ease;
            overflow-x: hidden;
            white-space: nowrap;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed) !important;
        }

        .sidebar.collapsed .brand-link {
            display: none !important; 
        }

        .sidebar.collapsed .header-bar {
            justify-content: center !important; 
            padding-left: 0 !important;
        }

        .sidebar.collapsed #sidebarToggle i::before {
            content: "\f479";
            font-size: 1.8rem;
        }

        .sidebar.collapsed .link-text, 
        .sidebar.collapsed .user-name {
            display: none; 
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar.collapsed .nav-link {
            text-align: center;
            padding-left: 0;
            padding-right: 0;
            justify-content: center;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0 !important;
            font-size: 1.2rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            padding: 10px 15px;
        }

        .nav-link:hover, .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, .15);
            border-radius: 5px;
        }

        #sidebarToggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .collapsed-dot {
            display: block !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 5px; 
            transition: all 0.2s ease-in-out; 
        }

        .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.2); 
            transform: translateX(5px); 
        }

        .nav-link.active {
            color: var(--sidebar-bg) !important; 
            background-color: #fff !important;  
            font-weight: bold;                  
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            border-radius: 8px;                
        }
        
        /* Chỉnh lại icon khi active để nó cùng màu với chữ */
        .nav-link.active i {
            color: var(--sidebar-bg) !important;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .sidebar.show-mobile {
                left: 0;
                width: 280px !important;
            }

            #mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
            }

            #mobile-overlay.show {
                display: block;
            }
            
            #sidebarToggle { display: none; }
        }
    </style>
</head>
<body>
    <div id="mobile-overlay" onclick="toggleMobileMenu()"></div>

    <div id="app" class="d-flex">
        
        @auth
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white sidebar" id="sidebar">
            
            <div class="header-bar d-flex align-items-center justify-content-between mb-3 mb-md-0 me-md-auto text-white text-decoration-none w-100">
                <a href="/" class="d-flex align-items-center text-white text-decoration-none brand-link">
                    <i class="bi bi-clock-fill me-2 fs-4"></i>
                    <span class="fs-4 fw-bold brand-text">Scheduler</span>
                </a>

                <button id="sidebarToggle">
                    <i class="bi bi-chevron-left"></i>
                </button>
            </div>

            <hr>
            
            <ul class="nav nav-pills flex-column mb-auto">
                @if(Auth::user()->Role == 'Manager')
                    <li class="nav-item">
                        <a href="{{ route('manager.dashboard') }}" class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-table me-2"></i> 
                            <span class="link-text">Bảng lịch tuần</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.scheduling') }}" class="nav-link {{ request()->routeIs('manager.scheduling') ? 'active' : '' }}">
                            <i class="bi bi-calendar-week me-2"></i>
                            <span class="link-text">Bảng xếp lịch</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.employees') }}" class="nav-link {{ request()->routeIs('manager.employees') ? 'active' : '' }}">
                            <i class="bi bi-people me-2"></i>
                            <span class="link-text">Quản lý nhân viên</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.payroll') }}" class="nav-link {{ request()->routeIs('manager.payroll') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin me-2"></i>
                            <span class="link-text">Bảng lương</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }} position-relative">
                            <i class="bi bi-chat-dots-fill me-2"></i>
                            <span class="link-text">Nhắn tin nội bộ</span>
                            
                            @if(isset($globalUnreadMsgCount) && $globalUnreadMsgCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto link-text" style="font-size: 0.7rem;">
                                    {{ $globalUnreadMsgCount > 99 ? '99+' : $globalUnreadMsgCount }}
                                </span>

                                <span class="position-absolute bg-danger border border-light rounded-circle collapsed-dot" 
                                    style="top: 8px; right: 10px; width: 10px; height: 10px; display: none;">
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.profile') }}" class="nav-link {{ request()->routeIs('manager.profile') ? 'active' : '' }}">
                            <i class="bi bi-person-circle me-2"></i>
                            <span class="link-text">Hồ sơ</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check me-2"></i>
                            <span class="link-text">Lịch làm của tôi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.register') }}" class="nav-link {{ request()->routeIs('staff.register') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square me-2"></i>
                            <span class="link-text">Đăng ký lịch</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('staff.payroll') }}" class="nav-link {{ request()->routeIs('staff.payroll') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin me-2"></i>
                            <span class="link-text">Bảng lương</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }} position-relative">
                            <i class="bi bi-chat-dots-fill me-2"></i>
                            <span class="link-text">Nhắn tin nội bộ</span>
                            
                            @if(isset($globalUnreadMsgCount) && $globalUnreadMsgCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto link-text" style="font-size: 0.7rem;">
                                    {{ $globalUnreadMsgCount > 99 ? '99+' : $globalUnreadMsgCount }}
                                </span>

                                <span class="position-absolute bg-danger border border-light rounded-circle collapsed-dot" 
                                    style="top: 8px; right: 10px; width: 10px; height: 10px; display: none;">
                                </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('staff.profile') }}" class="nav-link {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
                            <i class="bi bi-person-circle me-2"></i>
                            <span class="link-text">Hồ sơ cá nhân</span>
                        </a>
                    </li>
                @endif
            </ul>
            
            <hr>
            
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle justify-content-center-collapsed" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-5 me-2"></i>
                    <strong class="user-name">{{ Auth::user()->FullName }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li class="nav-item mt-auto">
                        <a href="#" class="nav-link text-white" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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

        <main class="flex-grow-1" style="max-height: 100vh; overflow-y: auto;">
            <nav class="navbar navbar-light bg-white shadow-sm d-md-none mb-3 sticky-top">
                <div class="container-fluid">
                    <button class="btn btn-link text-dark p-0 me-3" onclick="toggleMobileMenu()">
                        <i class="bi bi-list fs-1"></i>
                    </button>
                    <span class="navbar-brand mb-0 h1 fw-bold text-primary">Scheduler</span>
                    
                    @auth
                    <div class="flex-shrink-0">
                         <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; font-size: 0.9rem;">
                            {{ substr(Auth::user()->FullName, 0, 1) }}
                        </div>
                    </div>
                    @endauth
                </div>
            </nav>

            <div class="p-2 p-md-4">
                @if(Auth::check() && Auth::user()->EndDate)
                    <div class="alert alert-warning text-center m-0 rounded-0 fw-bold mb-3">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Tài khoản này đã ngưng hoạt động (Nghỉ việc). Bạn chỉ có quyền xem dữ liệu.
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