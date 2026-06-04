@extends('layouts.app')

@section('content')
<style>
    /* --- CSS CHUNG CHO GIAO DIỆN --- */
    .chat-container {
        height: 85vh; /* Chiều cao mặc định trên PC */
    }

    .user-item.active {
        background-color: #e9f2ff !important;
        color: #212529 !important;
        border-color: #dee2e6; 
        border-left: 4px solid #0d6efd !important;
    }
    
    .user-item.active .text-muted {
        color: #6c757d !important; 
    }

    .user-item:hover {
        background-color: #f8f9fa;
    }
    
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #aaa; }

    /* --- TỐI ƯU HÓA ĐỘC QUYỀN CHO ĐIỆN THOẠI (MOBILE APP STYLE) --- */
    @media (max-width: 767px) {
        .chat-container { 
            /* Sử dụng dvh thay vì vh để tự động tính toán trừ đi thanh địa chỉ của Safari/Chrome */
            height: calc(100dvh - 60px) !important; 
            border-radius: 0 !important; 
            border: none !important;
        }
        
        /* Mặc định: Hiện danh bạ, Ẩn khung chat */
        #chat-col-left { display: flex !important; width: 100%; }
        #chat-col-right { display: none !important; width: 100%; }

        /* Khi bấm vào 1 người: Ẩn danh bạ, Hiện khung chat */
        .chat-active #chat-col-left { display: none !important; }
        .chat-active #chat-col-right { display: flex !important; }
    }
</style>

{{-- Bỏ padding trên mobile (p-0) để giao diện tràn viền chuẩn App --}}
<div class="container-fluid p-0 p-md-4">
    {{-- Thêm g-0 để xóa khe hở (gutter) mặc định của row --}}
    <div id="chat-wrapper" class="row g-0 rounded-0 rounded-md-3 shadow-sm bg-white overflow-hidden chat-container">
        
        {{-- CỘT TRÁI: DANH BẠ --}}
        <div id="chat-col-left" class="col-md-4 col-lg-3 border-end p-0 d-flex flex-column bg-white h-100">
            
            <div class="p-3 border-bottom bg-white fw-bold text-primary flex-shrink-0 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill"></i> Danh bạ</span>
            </div>

            <div class="p-2 bg-light border-bottom flex-shrink-0">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="user-search" class="form-control border-start-0 ps-0" placeholder="Tìm tên hoặc tài khoản...">
                </div>
            </div>
            
            <div class="list-group list-group-flush flex-grow-1" id="user-list" style="height: 0; min-height: 0; overflow-y: auto;">
                @foreach($users as $user)
                <a href="#" class="list-group-item list-group-item-action user-item border-bottom-0" 
                   data-id="{{ $user->UserID }}" 
                   data-username="{{ $user->UserName }}"
                   onclick="openMobileChat()"> 
                   <div class="d-flex align-items-center w-100">
                        <div class="bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex justify-content-center align-items-center me-3 position-relative flex-shrink-0" style="width: 45px; height: 45px;">
                                {{ substr($user->FullName, 0, 1) }}
                                @if($user->unread_count > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger badge-unread border border-white">
                                    {{ $user->unread_count > 9 ? '9+' : $user->unread_count }}
                                </span>
                                @endif
                        </div>
                        <div class="overflow-hidden">
                            <div class="fw-bold text-dark text-truncate">{{ $user->FullName }}</div>
                            <div class="small text-muted">{{ $user->Role }}</div>
                        </div>
                    </div>
                </a>
                @endforeach
                
                <div id="no-result" class="text-center p-4 text-muted d-none">
                    <i class="bi bi-search display-6 mb-2"></i><br>
                    Không tìm thấy ai.
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: KHUNG CHAT --}}
        <div id="chat-col-right" class="col-md-8 col-lg-9 p-0 d-flex flex-column bg-white h-100">
            
            <div class="p-2 p-md-3 border-bottom bg-white d-flex align-items-center shadow-sm flex-shrink-0" style="min-height: 65px;">
                {{-- Nút Quay Lại (Chỉ hiện trên Mobile) --}}
                <button class="btn btn-light text-dark p-2 me-2 d-md-none rounded-circle" onclick="closeMobileChat()">
                    <i class="bi bi-arrow-left fs-5"></i>
                </button>
                
                <h5 class="m-0 text-truncate" id="chat-header">
                    <span class="text-muted fw-light">Chọn một người để bắt đầu...</span>
                </h5>
            </div>

            <div class="flex-grow-1 p-3 p-md-4" id="chat-box" style="background-color: #f5f7fb; height: 0; min-height: 0; overflow-y: auto;">
                {{-- Tin nhắn sẽ được load vào đây --}}
            </div>
            
            <div class="p-2 p-md-3 border-top bg-white d-none flex-shrink-0 shadow-sm" id="input-area">
                <div class="input-group input-group-lg">
                    <input type="text" id="message-input" class="form-control fs-6" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button class="btn btn-primary px-4" id="btn-send"><i class="bi bi-send-fill"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let receiverId = null;
    const myId = {{ Auth::id() }};

    $(document).ready(function() {
        
        $('#user-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var hasResult = false;
            $('.user-item').filter(function() {
                var name = $(this).find('.fw-bold').text().toLowerCase();
                var username = $(this).data('username').toString().toLowerCase();
                var match = name.indexOf(value) > -1 || username.indexOf(value) > -1;
                $(this).toggle(match);
                if(match) hasResult = true;
            });
            if (!hasResult) $('#no-result').removeClass('d-none');
            else $('#no-result').addClass('d-none');
        });

        $('.user-item').click(function(e) {
            e.preventDefault();
            $('.user-item').removeClass('active');
            $(this).addClass('active');
            $(this).find('.badge-unread').fadeOut();

            receiverId = $(this).data('id');
            const name = $(this).find('.fw-bold').text();
            
            $('#chat-header').html(`<strong class="text-primary">${name}</strong>`);
            $('#input-area').removeClass('d-none');
            $('#message-input').focus();
            loadMessages();
        });

        function loadMessages() {
            if(!receiverId) return;
            $.ajax({
                url: '/chat/messages/' + receiverId,
                method: 'GET',
                success: function(messages) {
                    $('#chat-box').empty();
                    messages.forEach(msg => {
                        let isMe = (msg.SenderID == myId);
                        let align = isMe ? 'justify-content-end' : 'justify-content-start';
                        let msgColor = isMe ? 'bg-primary text-white' : 'bg-white border text-dark shadow-sm';
                        
                        let time = new Date(msg.SendTime);
                        let timeStr = time.getHours().toString().padStart(2, '0') + ':' + time.getMinutes().toString().padStart(2, '0');

                        let html = `
                            <div class="d-flex ${align} mb-3">
                                <div style="max-width: 85%;">
                                    <div class="p-2 px-3 rounded-3 ${msgColor}" style="word-wrap: break-word;">
                                        ${msg.Content}
                                    </div>
                                    <div class="small text-muted mt-1 ${isMe ? 'text-end' : 'text-start'}" style="font-size: 0.7rem;">
                                        ${timeStr}
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#chat-box').append(html);
                    });
                    
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                }
            });
        }

        $('#btn-send').click(function() { sendMessage(); });
        $('#message-input').keypress(function(e) { if(e.which == 13) sendMessage(); });

        function sendMessage() {
            let msg = $('#message-input').val();
            if(msg.trim() == '') return;
            $.ajax({
                url: '/chat/send',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    receiver_id: receiverId,
                    message: msg
                },
                success: function(res) {
                    $('#message-input').val('');
                    loadMessages(); 
                }
            });
        }

        setInterval(function() {
            if(receiverId) loadMessages();
        }, 3000);
    });

    function openMobileChat() {
        if (window.innerWidth < 768) {
            document.getElementById('chat-wrapper').classList.add('chat-active');
        }
    }

    function closeMobileChat() {
        document.getElementById('chat-wrapper').classList.remove('chat-active');
        $('.user-item').removeClass('active');
        receiverId = null; 
    }
</script>
@endsection