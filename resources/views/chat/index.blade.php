@extends('layouts.app')

@section('content')
<style>
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
</style>

<div class="container-fluid py-4">
    <div class="row rounded-3 shadow-sm bg-white overflow-hidden" style="height: 85vh;">
        
        <div class="col-md-3 border-end p-0 d-flex flex-column bg-white">
            
            <div class="p-3 border-bottom bg-white fw-bold text-primary">
                <i class="bi bi-people-fill"></i> Danh bạ nhân viên
            </div>

            <div class="p-2 bg-light border-bottom">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="user-search" class="form-control border-start-0 ps-0" placeholder="Tìm tên hoặc tài khoản...">
                </div>
            </div>

            <div class="list-group list-group-flush overflow-auto flex-grow-1" id="user-list">
                @foreach($users as $user)
                <a href="#" class="list-group-item list-group-item-action user-item border-bottom-0" 
                   data-id="{{ $user->UserID }}" 
                   data-username="{{ $user->UserName }}"> <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center w-100">
                            <div class="bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex justify-content-center align-items-center me-3 position-relative flex-shrink-0" style="width: 45px; height: 45px;">
                                {{ substr($user->FullName, 0, 1) }}
                                
                                {{-- Chấm đỏ thông báo --}}
                                @if($user->unread_count > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger badge-unread border border-white">
                                    {{ $user->unread_count > 9 ? '9+' : $user->unread_count }}
                                </span>
                                @endif
                            </div>
                            
                            <div class="overflow-hidden">
                                <div class="fw-bold text-dark text-truncate">{{ $user->FullName }}</div>
                                <div class="small text-muted d-flex justify-content-between">
                                    <span>{{ $user->Role }}</span>
                                    {{-- <span style="font-size: 0.7em; opacity: 0.7">({{ $user->UserName }})</span> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
                
                {{-- Thông báo khi không tìm thấy --}}
                <div id="no-result" class="text-center p-4 text-muted d-none">
                    <i class="bi bi-search display-6 mb-2"></i><br>
                    Không tìm thấy ai.
                </div>
            </div>
        </div>

        <div class="col-md-9 p-0 d-flex flex-column bg-white">
            
            <div class="p-3 border-bottom bg-white d-flex align-items-center shadow-sm" style="height: 65px;">
                <h5 class="m-0 text-truncate" id="chat-header">
                    <span class="text-muted fw-light">Chọn một người để bắt đầu...</span>
                </h5>
            </div>

            <div class="flex-grow-1 p-4 overflow-auto" id="chat-box" style="background-color: #f5f7fb;">
                </div>

            <div class="p-3 border-top bg-white d-none" id="input-area">
                <div class="input-group">
                    <input type="text" id="message-input" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button class="btn btn-primary px-4" id="btn-send"><i class="bi bi-send-fill"></i> Gửi</button>
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
        
        // 1. LOGIC TÌM KIẾM NGƯỜI DÙNG
        $('#user-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            var hasResult = false;

            $('.user-item').filter(function() {
                // Lấy tên hiển thị
                var name = $(this).find('.fw-bold').text().toLowerCase();
                // Lấy username
                var username = $(this).data('username').toString().toLowerCase();
                
                // Kiểm tra xem từ khóa có nằm trong Tên hoặc Username không
                var match = name.indexOf(value) > -1 || username.indexOf(value) > -1;
                
                $(this).toggle(match);
                if(match) hasResult = true;
            });

            // Hiện thông báo nếu không tìm thấy ai
            if (!hasResult) {
                $('#no-result').removeClass('d-none');
            } else {
                $('#no-result').addClass('d-none');
            }
        });

        // 2. Khi bấm chọn user
        $('.user-item').click(function(e) {
            e.preventDefault();
            $('.user-item').removeClass('active');
            $(this).addClass('active');
            
            // Ẩn chấm đỏ
            $(this).find('.badge-unread').fadeOut();

            receiverId = $(this).data('id');
            const name = $(this).find('.fw-bold').text();
            
            $('#chat-header').html(`<strong class="text-primary">${name}</strong>`);
            $('#input-area').removeClass('d-none');
            $('#message-input').focus();
            loadMessages();
        });

        // 3. Hàm tải tin nhắn
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
                                <div style="max-width: 75%;">
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
                    
                    // Cuộn xuống dưới cùng
                    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
                }
            });
        }

        // 4. Gửi tin nhắn
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

        // 5. Auto refresh
        setInterval(function() {
            if(receiverId) loadMessages();
        }, 3000);
    });
</script>
@endsection