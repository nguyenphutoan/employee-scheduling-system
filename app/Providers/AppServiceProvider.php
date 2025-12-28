<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // Import View
use Illuminate\Support\Facades\Auth; // Import Auth
use App\Models\Message;              // Import Model Message

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Chia sẻ biến $globalUnreadMsgCount cho tất cả các view
        View::composer('*', function ($view) {
            $count = 0;
            if (Auth::check()) {
                $count = Message::where('ReceiverID', Auth::id())
                                ->where('IsRead', false)
                                ->count();
            }
            $view->with('globalUnreadMsgCount', $count);
        });
    }
}