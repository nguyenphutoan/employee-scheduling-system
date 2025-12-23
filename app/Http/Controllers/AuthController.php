<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // 1. Hiển thị form đăng nhập
    public function showLogin() {
        return view('login');
    }

    // 2. Xử lý đăng nhập
    public function login(Request $request) {
        // Validate dữ liệu
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $credentials = [
            'UserName' => $request->username, 
            'password' => $request->password
        ];

        // Auth::attempt trả về true nếu đúng User & Pass
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Bảo mật session

            // Kiểm tra quyền để chuyển hướng
            $role = Auth::user()->Role;
            
            if ($role === 'Manager') {
                return redirect()->route('manager.dashboard');
            } else {
                return redirect()->route('staff.dashboard');
            }
        }

        // Nếu sai thì quay lại và báo lỗi
        return back()->withErrors([
            'username' => 'Tên đăng nhập hoặc mật khẩu không đúng.',
        ]);
    }

    // 3. Đăng xuất
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}