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
        .sidebar {
            min-height: 100vh;
            background-color: #0D6EFD;
        }
        .nav-link {
            color: rgba(33, 31, 31, 0.75);
        }
        .nav-link:hover, .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
        }
    </style>
</head>
<body>
    <div id="app" class="d-flex">
        
        {{-- 1. SIDEBAR (THANH BÊN TRÁI) --}}
        @auth
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white sidebar" style="width: 280px;">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <i class="bi bi-grid-3x3-gap-fill me-2 fs-4"></i>
                <span class="fs-4 fw-bold">Scheduler</span>
            </a>
            <hr>
            
            <ul class="nav nav-pills flex-column mb-auto">
            
                
                @if(Auth::user()->Role == 'Manager')
                    {{-- === MENU QUẢN LÝ === --}}
                    <li class="nav-item">
                        <a href="{{ route('manager.dashboard') }}" 
                        class="nav-link {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-table me-2"></i> {{-- Icon cái bảng --}}
                            Bảng lịch tuần
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('manager.scheduling') }}" 
                        class="nav-link {{ request()->routeIs('manager.scheduling') ? 'active' : '' }}">
                            <i class="bi bi-calendar-week me-2"></i> {{-- Icon tờ lịch --}}
                            Bảng xếp lịch
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('manager.employees') }}" 
                        class="nav-link {{ request()->routeIs('manager.employees') ? 'active' : '' }}">
                            <i class="bi bi-people me-2"></i>
                            Quản lý nhân viên
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('manager.payroll') }}" 
                        class="nav-link {{ request()->routeIs('manager.payroll') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin me-2"></i>
                            Bảng lương
                        </a>
                    </li>
                    <li class="nav-item">
                    <a href="{{ route('manager.profile') }}" 
                    class="nav-link {{ request()->routeIs('manager.profile') ? 'active' : '' }}">
                        <i class="bi bi-person-circle me-2"></i>
                        Hồ sơ
                    </a>
                    </li>
                @else
                    {{-- === MENU NHÂN VIÊN === --}}
                    <li>
                        <a href="{{ route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check me-2"></i>
                            Lịch làm của tôi
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.register') }}" class="nav-link {{ request()->routeIs('staff.register') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square me-2"></i>
                            Đăng ký lịch
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('staff.payroll') }}" 
                        class="nav-link {{ request()->routeIs('staff.payroll') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin me-2"></i>
                            Bảng lương
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('staff.profile') }}" 
                        class="nav-link {{ request()->routeIs('staff.profile') ? 'active' : '' }}">
                            <i class="bi bi-person-circle me-2"></i>
                            Hồ sơ cá nhân
                        </a>
                    </li>
                @endif
            </ul>
            
            <hr>
            
            {{-- THÔNG TIN USER & ĐĂNG XUẤT --}}
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    {{-- Chỉ hiện tên --}}
                    <strong>{{ Auth::user()->FullName }}</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li class="nav-item mt-auto">
                        {{-- 1. Tạo một link giả để người dùng bấm vào --}}
                        <a href="#" class="nav-link text-white" 
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Đăng xuất
                        </a>

                        {{-- 2. Tạo một form ẩn chứa CSRF Token để gửi lệnh POST thực sự --}}
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        @endauth

        {{-- 2. MAIN CONTENT (NỘI DUNG CHÍNH BÊN PHẢI) --}}
        <main class="flex-grow-1" style="max-height: 100vh; overflow-y: auto;">
            <nav class="navbar navbar-light bg-white shadow-sm d-md-none mb-3">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">Scheduler</span>
                </div>
            </nav>

            <div class="p-4">
                @yield('content')
            </div>
        </main>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>