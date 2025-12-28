<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{

    public function index()
    {
        $myId = Auth::id();

        $users = User::where('UserID', '!=', $myId)
            ->withCount(['messagesSent as unread_count' => function($q) use ($myId) {
                $q->where('ReceiverID', $myId)->where('IsRead', false);
            }])
            ->get();

        return view('chat.index', compact('users'));
    }

    // 2. lấy tin nhắn giữa mình và UserID khác
    public function fetchMessages($userId)
    {
        $myId = Auth::id();
        // Cập nhật tất cả tin nhắn từ người đó gửi cho mình thành đã xem
        Message::where('SenderID', $userId)
               ->where('ReceiverID', $myId)
               ->where('IsRead', false)
               ->update(['IsRead' => true]);

        // 2. Lấy danh sách tin nhắn
        $messages = Message::where(function($q) use ($myId, $userId) {
                        $q->where('SenderID', $myId)->where('ReceiverID', $userId);
                    })
                    ->orWhere(function($q) use ($myId, $userId) {
                        $q->where('SenderID', $userId)->where('ReceiverID', $myId);
                    })
                    ->orderBy('SendTime', 'asc')
                    ->get();

        return response()->json($messages);
    }

    // 3. API Gửi tin nhắn
    public function sendMessage(Request $request)
    {
        $message = Message::create([
            'SenderID' => Auth::id(),
            'ReceiverID' => $request->receiver_id,
            'Content' => $request->message,
            'SendTime' => Carbon::now(),
            'IsRead' => false
        ]);

        return response()->json(['status' => 'success', 'data' => $message]);
    }
}