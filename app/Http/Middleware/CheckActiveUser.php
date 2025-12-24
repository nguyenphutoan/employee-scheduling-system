<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra: Nếu đã đăng nhập VÀ có EndDate (tức là đã nghỉ)
        if (Auth::check() && !is_null(Auth::user()->EndDate)) {
            
            //chặn truy cập vào các route được bảo vệ bởi middleware này
            return back()->with('error', 'Không thể thực hiện do đã nghỉ làm.');
        }

        return $next($request);
    }
}