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
    
    ::-webkit-scrollbar {
        width: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1; 
    }
    ::-webkit-scrollbar-thumb {
        background: #ccc; 
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #aaa; 
    }

    @media (max-width: 768px) {
        #chat-col-right { display: none !important; }
        #chat-col-left { display: flex !important; width: 100%; }

        .chat-active #chat-col-left { display: none !important; }
        .chat-active #chat-col-right { display: flex !important; width: 100%; }
        
        .chat-container { height: calc(100vh - 140px) !important; }
    }
</style>

<div class="container-fluid py-0 py-md-4">
    <div id="chat-wrapper" class="row rounded-3 shadow-sm bg-white overflow-hidden chat-container" style="height: 85vh;">
        
        <div id="chat-col-left" class="col-md-3 border-end p-0 d-flex flex-column bg-white h-100">
            <div class="p-3 border-bottom bg-white fw-bold text-primary flex-shrink-0">
                <i class="bi bi-people-fill"></i> Danh bạ
            </div>
            <div class="list-group list-group-flush flex-grow-1" id="user-list" style="height: 0; min-height: 0; overflow-y: auto;">
                @foreach($users as $user)
                <a href="#" class="list-group-item list-group-item-action user-item border-bottom-0" 
                   data-id="{{ $user->UserID }}" 
                   data-username="{{ $user->UserName }}"
                   onclick="openMobileChat()"> <div class="d-flex align-items-center w-100">
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
            </div>
        </div>

        <div id="chat-col-right" class="col-md-9 p-0 d-flex flex-column bg-white h-100">
            
            <div class="p-3 border-bottom bg-white d-flex align-items-center shadow-sm flex-shrink-0" style="height: 65px;">
                <button class="btn btn-link text-dark p-0 me-3 d-md-none" onclick="closeMobileChat()">
                    <i class="bi bi-arrow-left fs-2"></i>
                </button>
                
                <h5 class="m-0 text-truncate" id="chat-header">
                    <span class="text-muted fw-light">Chọn người để chat...</span>
                </h5>
            </div>

            <div class="flex-grow-1 p-4" id="chat-box" style="background-color: #f5f7fb; height: 0; min-height: 0; overflow-y: auto;"></div>
            
            <div class="p-2 p-md-3 border-top bg-white d-none flex-shrink-0" id="input-area">
                <div class="input-group">
                    <input type="text" id="message-input" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button class="btn btn-primary" id="btn-send"><i class="bi bi-send-fill"></i></button>
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