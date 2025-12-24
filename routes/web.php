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
        // ====================================================
        // (Manager đã nghỉ việc vẫn có thể truy cập các trang này)
        // ====================================================
        // 1. Dashboard & Lịch (Chỉ xem)
        Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
        Route::get('/scheduling', [ManagerController::class, 'scheduling'])->name('scheduling'); // Vào trang xếp lịch xem, nhưng không lưu được

        // 2. Xem danh sách nhân viên
        Route::get('/employees', [ManagerController::class, 'indexEmployees'])->name('employees');

        // 3. Xem hồ sơ & Bảng lương
        Route::get('/profile', [ManagerController::class, 'showProfile'])->name('profile');
        Route::get('/payroll', [ManagerController::class, 'payroll'])->name('payroll');


        // ====================================================
        // (Sẽ bị CHẶN nếu tài khoản có EndDate khác null)
        // ====================================================
        Route::middleware(['active.user'])->group(function () {
            
            // 1. Thao tác với Tuần làm việc (Tạo mới, Chốt lịch)
            Route::post('/create-week', [ManagerController::class, 'createWeek'])->name('create_week');
            Route::post('/submit-week', [ManagerController::class, 'submitWeek'])->name('submit_week');
            
            // 2. Thao tác Phân công (Gán, Xóa ca)
            Route::post('/assign', [ManagerController::class, 'assignStaff'])->name('assign');
            Route::delete('/delete-assignment/{id}', [ManagerController::class, 'deleteAssignment'])->name('delete_assignment');
            Route::post('/manager/approve-assignment/{id}', [ManagerController::class, 'approveAssignment'])
                ->name('approve_assignment');

            // 3. Quản lý Nhân viên (Thêm, Sửa, Xóa)
            Route::post('/employees', [ManagerController::class, 'storeEmployee'])->name('employees.store');
            Route::put('/employees/{id}', [ManagerController::class, 'updateEmployee'])->name('employees.update');
            Route::delete('/employees/{id}', [ManagerController::class, 'deleteEmployee'])->name('employees.delete');

            // 4. Cập nhật hồ sơ cá nhân (Đổi tên, pass...)
            Route::post('/profile', [ManagerController::class, 'updateProfile'])->name('profile.update');
        });

    });

    // ----------------------------------------------------
    // GROUP B: STAFF (NHÂN VIÊN)
    // ----------------------------------------------------
    // Prefix URL: /staff/... | Prefix Name: staff....
    // GROUP B: STAFF (NHÂN VIÊN)
    Route::prefix('staff')->name('staff.')->group(function () {
        
        // --- 1. CÁC ROUTE CHỈ XEM (Vẫn cho phép người nghỉ làm truy cập) ---
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
        Route::get('/register', [StaffController::class, 'index'])->name('register'); // Xem form đăng ký
        Route::get('/payroll', [StaffController::class, 'payroll'])->name('payroll');
        Route::get('/profile', [StaffController::class, 'showProfile'])->name('profile');


        // --- 2. CÁC ROUTE THAO TÁC (Chặn người đã nghỉ làm) ---
        Route::middleware(['active.user'])->group(function () {
            
            // Lưu đăng ký lịch
            Route::post('/register', [StaffController::class, 'store'])->name('store');   
            
            // Xác nhận công (Tick)
            Route::post('/confirm-assignment/{id}', [StaffController::class, 'confirmAssignment'])->name('confirm_assignment');
            
            // Đổi mật khẩu
            Route::post('/profile', [StaffController::class, 'updateProfile'])->name('profile.update');
        });

    });

});