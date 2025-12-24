@extends('layouts.app')

@section('title', 'Xếp lịch làm việc')

@section('content')
<div class="container-fluid">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <a href="{{ route('manager.scheduling', ['date' => $prevWeekDate]) }}" class="btn btn-outline-primary">
            &laquo; Tuần trước
        </a>

        <div class="text-center">
            <h4 class="mb-0 fw-bold text-uppercase">Lịch làm việc</h4>
            <span class="text-muted">
                Đang xem ngày: <strong>{{ date('d/m/Y', strtotime($selectedDate)) }}</strong>
            </span>
        </div>

        <a href="{{ route('manager.scheduling', ['date' => $nextWeekDate]) }}" class="btn btn-outline-primary">
            Tuần tới &raquo;
        </a>
    </div>

    {{-- KIỂM TRA: NẾU ĐÃ CÓ DỮ LIỆU CA LÀM VIỆC --}}
    @if($morningShift && $eveningShift) 

        {{-- 2. THANH TAB CHỌN NGÀY TRONG TUẦN --}}
        <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm justify-content-center">
            @foreach($weekDates as $day)
            <li class="nav-item mx-1">
                <a class="nav-link {{ $day['isActive'] ? 'active' : '' }} text-center border" 
                   href="{{ route('manager.scheduling', ['date' => $day['date']]) }}"
                   style="min-width: 100px;">
                    <div class="fw-bold">{{ $day['dayName'] }}</div>
                    <small>{{ date('d/m', strtotime($day['date'])) }}</small>
                </a>
            </li>
            @endforeach
        </ul>

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

    @else
        {{-- TRƯỜNG HỢP: CHƯA CÓ DỮ LIỆU TUẦN --}}
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <div class="mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="100" alt="No Data" style="opacity: 0.5">
            </div>
            <h3 class="text-muted">Chưa có lịch làm việc cho tuần này</h3>
            <p class="text-secondary">Bạn cần khởi tạo cấu trúc tuần và các ca làm việc trước khi bắt đầu xếp lịch.</p>
            
            <form action="{{ route('manager.create_week') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $selectedDate }}">
                <button type="submit" class="btn btn-primary btn-lg px-4 shadow">
                    ✨ Khởi tạo lịch tuần này ngay
                </button>
            </form>
        </div>
    @endif
</div>

{{-- 4. MODAL CHỌN NHÂN VIÊN --}}
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn nhân viên vào vị trí: <span id="modalPosName" class="text-primary fw-bold"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('manager.assign') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="shift_id" id="modalShiftId">
                    <input type="hidden" name="position_id" id="modalPosId">
                    <input type="hidden" name="date" value="{{ $selectedDate }}">

                    <label class="form-label fw-bold">Danh sách nhân viên đăng ký rảnh:</label>
                    <div class="list-group mb-3" style="max-height: 300px; overflow-y: auto;">
                        @forelse($availableStaffs as $avail)
                            {{-- KIỂM TRA QUAN TRỌNG: Chỉ hiện nếu User tồn tại --}}
                            @if($avail->user)
                                <label class="list-group-item d-flex gap-3 cursor-pointer list-group-item-action">
                                    <input class="form-check-input flex-shrink-0 user-select-radio" 
                                        type="radio" 
                                        name="user_id" 
                                        value="{{ $avail->user->UserID }}" 
                                        data-start="{{ substr($avail->AvailableFrom, 0, 5) }}"
                                        data-end="{{ substr($avail->AvailableTo, 0, 5) }}"
                                        required>
                                    <span>
                                        <strong>{{ $avail->user->FullName }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            🕒 Rảnh: {{ substr($avail->AvailableFrom, 0, 5) }} - {{ substr($avail->AvailableTo, 0, 5) }}
                                        </small>
                                    </span>
                                </label>
                            @endif
                        @empty
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-exclamation-triangle"></i> Không có nhân viên nào đăng ký rảnh vào ngày này!
                            </div>
                        @endforelse
                    </div>

                    <div class="card bg-light border-0 p-3">
                        <label class="fw-bold mb-2">Giờ làm việc thực tế:</label>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="flex-grow-1">
                                <label class="small text-muted">Bắt đầu</label>
                                <input type="text" name="start_time" id="inputStartTime" class="form-control" placeholder="00:00" required>
                            </div>
                            <span class="fw-bold mt-3">-</span>
                            <div class="flex-grow-1">
                                <label class="small text-muted">Kết thúc</label>
                                <input type="text" name="end_time" id="inputEndTime" class="form-control" placeholder="00:00" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">💾 Lưu phân công</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(isset($currentWeek) && $currentWeek)
    <div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1000;">
        <form action="{{ route('manager.submit_week') }}" method="POST" 
              onsubmit="return confirm('XÁC NHẬN: Bạn muốn đăng lịch tuần này?\n\n- Nhân viên sẽ thấy lịch ngay lập tức.');">
            @csrf
            
            {{-- Dòng gây lỗi cũ: value="{{ $currentWeek->WeekID }}" --}}
            <input type="hidden" name="week_id" value="{{ $currentWeek->WeekID }}">
            
            <button type="submit" class="btn btn-success btn-lg shadow-lg rounded-pill fw-bold px-4 py-3">
                <i class="bi bi-send-check-fill me-2"></i> ĐĂNG LỊCH
            </button>
        </form>
    </div>
@endif

{{-- 5. JAVASCRIPT XỬ LÝ --}}
<script>
    // Hàm mở Modal và reset form
    function openAssignModal(shiftId, posId, posName) {
        // Gán giá trị vào input ẩn
        document.getElementById('modalShiftId').value = shiftId;
        document.getElementById('modalPosId').value = posId;
        document.getElementById('modalPosName').innerText = posName;
        
        // Reset: Bỏ chọn radio và xóa giờ cũ
        document.querySelectorAll('input[name="user_id"]').forEach(el => el.checked = false);
        document.getElementById('inputStartTime').value = '';
        document.getElementById('inputEndTime').value = '';

        // Hiển thị Modal
        var myModal = new bootstrap.Modal(document.getElementById('assignModal'));
        myModal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {

        flatpickr("#inputStartTime", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i", // Định dạng 24h
            time_24hr: true,   
            static: true      
        });

        flatpickr("#inputEndTime", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            static: true
        });
        const staffRadios = document.querySelectorAll('.user-select-radio');
        const startInput = document.getElementById('inputStartTime');
        const endInput = document.getElementById('inputEndTime');

        staffRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                // Lấy giờ rảnh từ data attribute
                const timeStart = this.getAttribute('data-start');
                const timeEnd = this.getAttribute('data-end');

                // Điền vào ô input
                if(timeStart) startInput.value = timeStart;
                if(timeEnd) endInput.value = timeEnd;

                // Hiệu ứng nháy nhẹ để báo hiệu đã điền
                startInput.style.transition = "background-color 0.3s";
                endInput.style.transition = "background-color 0.3s";
                startInput.style.backgroundColor = "#d1e7dd";
                endInput.style.backgroundColor = "#d1e7dd";
                
                setTimeout(() => {
                    startInput.style.backgroundColor = "";
                    endInput.style.backgroundColor = "";
                }, 500);
            });
        });
    });
</script>
@endsection