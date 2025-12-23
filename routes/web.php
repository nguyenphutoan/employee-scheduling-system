<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\StaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ====================================================
// 1. PUBLIC ROUTES (KHÔNG CẦN ĐĂNG NHẬP)
// ====================================================

// Trang chủ mặc định -> Chuyển hướng về Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Đăng nhập
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');


// ====================================================
// 2. AUTHENTICATED ROUTES (PHẢI ĐĂNG NHẬP)
// ====================================================
Route::middleware(['auth'])->group(function () {

    // Đăng xuất (Dùng POST để bảo mật tránh lỗi 419)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ----------------------------------------------------
    // GROUP A: MANAGER (QUẢN LÝ)
    // ----------------------------------------------------
    // Prefix URL: /manager/... | Prefix Name: manager....
    Route::prefix('manager')->name('manager.')->group(function () {
        
        // 1. Dashboard & Lịch
        // Trang chủ quản lý: Bảng tổng hợp lịch tuần (Matrix View)
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
        
        // Trang xếp lịch: Kéo thả/Chọn nhân viên (Cũ là dashboard, giờ đổi thành scheduling)
        Route::get('/scheduling', [ManagerController::class, 'scheduling'])->name('scheduling');

        // 2. Thao tác Xử lý Lịch
        Route::post('/create-week', [ManagerController::class, 'createWeek'])->name('create_week');
        Route::post('/submit-week', [ManagerController::class, 'submitWeek'])->name('submit_week');
        
        // 3. Thao tác Phân công (Assignment)
        Route::post('/assign', [ManagerController::class, 'assignStaff'])->name('assign');
        Route::delete('/delete-assignment/{id}', [ManagerController::class, 'deleteAssignment'])->name('delete_assignment');

        // 4. Quản lý Nhân viên (Employees CRUD)
        Route::get('/employees', [ManagerController::class, 'indexEmployees'])->name('employees');
        Route::post('/employees', [ManagerController::class, 'storeEmployee'])->name('employees.store');       // Thêm
        Route::put('/employees/{id}', [ManagerController::class, 'updateEmployee'])->name('employees.update'); // Sửa
        Route::delete('/employees/{id}', [ManagerController::class, 'deleteEmployee'])->name('employees.delete'); // Xóa

        // Quản lý hồ sơ cá nhân
        Route::get('/profile', [ManagerController::class, 'showProfile'])->name('profile');
        Route::post('/profile', [ManagerController::class, 'updateProfile'])->name('profile.update');

        // Bảng lương tổng hợp
        Route::get('/payroll', [ManagerController::class, 'payroll'])->name('payroll');
    });

    // ----------------------------------------------------
    // GROUP B: STAFF (NHÂN VIÊN)
    // ----------------------------------------------------
    // Prefix URL: /staff/... | Prefix Name: staff....
    Route::prefix('staff')->name('staff.')->group(function () {
        
        // Xem lịch cá nhân
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');

        // Đăng ký lịch rảnh
        Route::get('/register', [StaffController::class, 'index'])->name('register'); // Form đăng ký
        Route::post('/register', [StaffController::class, 'store'])->name('store');   // Lưu đăng ký

        // HỒ SƠ CÁ NHÂN
        Route::get('/profile', [StaffController::class, 'showProfile'])->name('profile');
        Route::post('/profile', [StaffController::class, 'updateProfile'])->name('profile.update');

        // BẢNG LƯƠNG
        Route::get('/payroll', [StaffController::class, 'payroll'])->name('payroll');
    });

    // 1. Route cho Nhân viên xác nhận công (Tick)
    Route::post('/staff/confirm-assignment/{id}', [StaffController::class, 'confirmAssignment'])
        ->name('staff.confirm_assignment');

    // 2. Route cho Quản lý duyệt công (Approve)
    Route::post('/manager/approve-assignment/{id}', [ManagerController::class, 'approveAssignment'])
        ->name('manager.approve_assignment');

});