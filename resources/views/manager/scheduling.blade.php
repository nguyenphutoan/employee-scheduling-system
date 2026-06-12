@extends('layouts.app')

@section('title', 'Xếp lịch làm việc')

@section('content')
<div class="container-fluid">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        /* Ẩn thanh cuộn ngang trên điện thoại nhưng vẫn cho vuốt */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        .btn-responsive { width: 100%; }
        @media (min-width: 768px) {
            .btn-responsive { width: auto; }
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5 align-middle"></i>
            <strong>Thất bại:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            ⛔ <strong>Có lỗi xảy ra:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 1. THANH ĐIỀU HƯỚNG TUẦN --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mb-4 bg-white p-3 rounded shadow-sm gap-3">
        
        <div class="order-2 order-md-1 text-md-start">
            <a href="{{ route('manager.scheduling', ['date' => $prevWeekDate]) }}" class="btn btn-outline-primary btn-responsive">
                &laquo; Tuần trước
            </a>
        </div>

        <div class="text-center order-1 order-md-2 flex-grow-1 px-2">
            <h4 class="mb-0 fw-bold text-uppercase" style="white-space: nowrap;">Lịch làm việc</h4>
            <span class="text-muted d-block mt-1" style="white-space: nowrap;">
                Đang xem ngày: <strong class="text-primary">{{ date('d/m/Y', strtotime($selectedDate)) }}</strong>
            </span>
        </div>

        <div class="order-3 order-md-3 text-md-end">
            <a href="{{ route('manager.scheduling', ['date' => $nextWeekDate]) }}" class="btn btn-outline-primary btn-responsive">
                Tuần tới &raquo;
            </a>
        </div>
    </div>

    {{-- KIỂM TRA: NẾU ĐÃ CÓ DỮ LIỆU CA LÀM VIỆC --}}
    @if($morningShift && $eveningShift) 

        {{-- 2. THANH TAB CHỌN NGÀY TRONG TUẦN --}}
        <div class="overflow-auto pb-2 mb-3 hide-scrollbar">
            <ul class="nav nav-pills flex-nowrap bg-white p-2 rounded shadow-sm" style="min-width: max-content;">
                @foreach($weekDates as $day)
                <li class="nav-item mx-1">
                    <a class="nav-link {{ $day['isActive'] ? 'active shadow' : 'bg-light text-dark border' }} text-center d-flex flex-column justify-content-center" 
                       href="{{ route('manager.scheduling', ['date' => $day['date']]) }}"
                       style="min-width: 100px; height: 70px;">
                        
                        <div class="fw-bold text-uppercase small">{{ $day['dayName'] }}</div>
                        <div class="small mt-1">{{ date('d/m', strtotime($day['date'])) }}</div>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- 3. INCLUDE CÁC BẢNG XẾP LỊCH (Partial Views) --}}
        @include('manager.partials.shift_table', [
            'shift' => $morningShift, 
            'title' => '☀️ CA SÁNG', 
            'positions' => $positions
        ])

        @include('manager.partials.shift_table', [
            'shift' => $eveningShift, 
            'title' => '🌙 CA TỐI', 
            'positions' => $positions
        ])

        @if(isset($currentWeek))
        <div class="mt-2 text-center text-md-start mb-5 mb-md-0">
            <button type="button" class="btn btn-info text-white rounded-pill px-4 py-2 shadow-sm btn-responsive" 
                    onclick="checkStatus({{ $currentWeek->WeekID }})">
                <i class="bi bi-list-check"></i> Xem tiến độ đăng ký của NV
            </button>
        </div>
        @endif

    @else
        {{-- TRƯỜNG HỢP: CHƯA CÓ DỮ LIỆU TUẦN --}}
        <div class="text-center py-5 bg-white rounded shadow-sm px-3">
            <div class="mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" alt="No Data" style="opacity: 0.5">
            </div>
            <h3 class="text-muted">Chưa có lịch làm việc cho tuần này</h3>
            <p class="text-secondary">Bạn cần khởi tạo cấu trúc tuần và các ca làm việc trước khi bắt đầu xếp lịch.</p>
            
            <form action="{{ route('manager.create_week') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <button type="submit" class="btn btn-primary btn-lg px-4 shadow btn-responsive">
                    ✨ Khởi tạo lịch tuần này ngay
                </button>
            </form>
        </div>
    @endif
</div>

{{-- 4. MODAL CHỌN NHÂN VIÊN --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Chọn nhân viên: <span id="modalPosName" class="fw-bold text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('manager.assign') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <input type="hidden" name="shift_id" id="modalShiftId">
                    <input type="hidden" name="position_id" id="modalPosId">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">

                    <label class="form-label fw-bold text-secondary">Danh sách nhân viên đăng ký rảnh:</label>
                    <div class="list-group mb-4 shadow-sm" style="max-height: 300px; overflow-y: auto;">
                        @forelse($availableStaffs as $avail)
                            @if($avail->user)
                                <label class="list-group-item d-flex gap-3 cursor-pointer list-group-item-action align-items-start">
                                    <input class="form-check-input flex-shrink-0 user-select-radio fs-5 mt-1" 
                                        type="radio" 
                                        name="user_id" 
                                        value="{{ $avail->user->UserID }}" 
                                        data-start="{{ substr($avail->slots->first()->AvailableFrom, 0, 5) }}"
                                        data-end="{{ substr($avail->slots->first()->AvailableTo, 0, 5) }}"
                                        required>
                                    <span>
                                        <strong class="fs-6">{{ $avail->user->FullName }}</strong>
                                        <br>
                                        @foreach($avail->slots as $slot)
                                            <span class="badge bg-light text-dark border mt-1">
                                                🕒 {{ substr($slot->AvailableFrom, 0, 5) }} - {{ substr($slot->AvailableTo, 0, 5) }}
                                            </span>
                                        @endforeach
                                    </span>
                                </label>
                            @endif
                        @empty
                            <div class="alert alert-warning text-center border-0 mb-0">
                                <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2 text-warning"></i> 
                                Không có nhân viên nào rảnh!
                            </div>
                        @endforelse
                    </div>

                    <div class="card bg-light border-0 p-3 shadow-sm">
                        <label class="fw-bold mb-2 text-primary">Giờ làm việc thực tế:</label>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="flex-grow-1">
                                <label class="small text-muted mb-1">Bắt đầu</label>
                                <input type="text" name="start_time" id="inputStartTime" class="form-control text-center fw-bold" placeholder="00:00" required>
                            </div>
                            <span class="fw-bold mt-4 text-muted">-</span>
                            <div class="flex-grow-1">
                                <label class="small text-muted mb-1">Kết thúc</label>
                                <input type="text" name="end_time" id="inputEndTime" class="form-control text-center fw-bold" placeholder="00:00" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">💾 Lưu phân công</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(isset($currentWeek) && $currentWeek)
    {{-- Nút ĐĂNG LỊCH dính góc dưới màn hình --}}
    <div class="position-fixed bottom-0 end-0 p-3 p-md-4" style="z-index: 1000;">
        <form action="{{ route('manager.submit_week') }}" method="POST" 
              onsubmit="return confirm('XÁC NHẬN: Bạn muốn đăng lịch tuần này?\n\n- Nhân viên sẽ thấy lịch ngay lập tức.');">
            @csrf
            <input type="hidden" name="week_id" value="{{ $currentWeek->WeekID }}">
            <button type="submit" class="btn btn-success btn-lg shadow-lg rounded-pill fw-bold px-4 py-3 border-2 border-white">
                <i class="bi bi-send-check-fill me-2"></i> ĐĂNG LỊCH
            </button>
        </form>
    </div>
@endif

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary">📋 Tình trạng đăng ký lịch tuần</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-6 border-end p-3">
                        <h6 class="text-success fw-bold mb-3 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-check-circle-fill"></i> Đã đăng ký</span>
                            <span class="badge bg-success rounded-pill fs-6" id="count-reg">0</span>
                        </h6>
                        <ul class="list-group list-group-flush shadow-sm rounded" id="list-registered" style="max-height: 400px; overflow-y: auto;"></ul>
                    </div>
                    
                    <div class="col-md-6 p-3 bg-light">
                        <h6 class="text-danger fw-bold mb-3 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-hourglass-split"></i> Chưa đăng ký</span>
                            <span class="badge bg-danger rounded-pill fs-6" id="count-not">0</span>
                        </h6>
                        <ul class="list-group list-group-flush shadow-sm rounded bg-white" id="list-not-registered" style="max-height: 400px; overflow-y: auto;"></ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

{{-- 5. JAVASCRIPT XỬ LÝ --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
    function openAssignModal(shiftId, posId, posName) {
        document.getElementById('modalShiftId').value = shiftId;
        document.getElementById('modalPosId').value = posId;
        document.getElementById('modalPosName').innerText = posName;
        document.querySelectorAll('input[name="user_id"]').forEach(el => el.checked = false);
        document.getElementById('inputStartTime').value = '';
        document.getElementById('inputEndTime').value = '';
        var myModal = new bootstrap.Modal(document.getElementById('assignModal'));
        myModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#inputStartTime", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, static: true });
        flatpickr("#inputEndTime", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, static: true });
        const staffRadios = document.querySelectorAll('.user-select-radio');
        const startInput = document.getElementById('inputStartTime');
        const endInput = document.getElementById('inputEndTime');

        staffRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const timeStart = this.getAttribute('data-start');
                const timeEnd = this.getAttribute('data-end');
                if(timeStart) startInput.value = timeStart;
                if(timeEnd) endInput.value = timeEnd;
                startInput.style.transition = "background-color 0.3s";
                endInput.style.transition = "background-color 0.3s";
                startInput.style.backgroundColor = "#d1e7dd";
                endInput.style.backgroundColor = "#d1e7dd";
                setTimeout(() => { startInput.style.backgroundColor = ""; endInput.style.backgroundColor = ""; }, 500);
            });
        });
    });

    function checkStatus(weekId) {
        $('#list-registered').html('<div class="text-center py-3"><div class="spinner-border text-primary"></div></div>');
        $('#list-not-registered').html('<div class="text-center py-3"><div class="spinner-border text-danger"></div></div>');
        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        statusModal.show();
        $.ajax({
            url: '/manager/check-availability/' + weekId,
            type: 'GET',
            success: function(res) {
                $('#list-registered').empty();
                $('#count-reg').text(res.registered.length);
                if (res.registered.length > 0) {
                    res.registered.forEach(user => {
                        $('#list-registered').append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><strong>${user.FullName}</strong><br><small class="text-muted">${user.email}</small></div>
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                            </li>
                        `);
                    });
                } else {
                    $('#list-registered').html('<li class="list-group-item text-muted text-center fst-italic">Chưa có ai đăng ký.</li>');
                }

                $('#list-not-registered').empty();
                $('#count-not').text(res.not_registered.length);
                if (res.not_registered.length > 0) {
                    res.not_registered.forEach(user => {
                        $('#list-not-registered').append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                <div><strong>${user.FullName}</strong><br><small class="text-muted">${user.email}</small></div>
                                <i class="bi bi-hourglass-split text-danger fs-5"></i>
                            </li>
                        `);
                    });
                } else {
                    $('#list-not-registered').html('<li class="list-group-item text-success text-center fw-bold">Tất cả nhân viên đã nộp lịch!</li>');
                }
            },
            error: function() { alert('Không thể tải dữ liệu. Vui lòng thử lại!'); }
        });
    }
</script>
@endsection