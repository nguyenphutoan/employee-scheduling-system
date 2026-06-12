@extends('layouts.app')

@section('title', 'Đăng ký lịch làm việc')

@section('content')
<div class="container">
    <style>
        /* Tùy chỉnh chiều rộng nút bấm cho responsive */
        .btn-responsive { width: 100%; }
        @media (min-width: 768px) {
            .btn-responsive { width: auto; }
        }

        /* Card ngày */
        .day-card {
            border: 1px solid #dee2e6;
            border-radius: 0.75rem;
            background: #fff;
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            overflow: hidden;
        }
        .day-card.has-slots {
            border-color: #198754;
            background-color: #f8fff8;
        }
        .day-card-header {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .day-card.has-slots .day-card-header {
            background-color: #d1e7dd;
            border-bottom-color: #badbcc;
        }
        .day-card-body {
            padding: 0.75rem 1rem;
        }

        /* Slot row */
        .slot-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            border: 1px solid #e9ecef;
        }
        .slot-row input[type="time"] {
            flex: 1;
            min-width: 0;
            font-size: 1rem;
            padding: 0.375rem 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            background-color: #fff;
            /* Cho phép bàn phím mở trên mobile */
            -webkit-appearance: none;
        }
        .slot-row .slot-separator {
            font-weight: bold;
            color: #6c757d;
            flex-shrink: 0;
        }
        .slot-row .btn-remove-slot {
            flex-shrink: 0;
        }

        /* Nút thêm khung giờ */
        .btn-add-slot {
            border: 2px dashed #0d6efd;
            background: transparent;
            color: #0d6efd;
            border-radius: 0.5rem;
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
        }
        .btn-add-slot:hover {
            background-color: #e7f1ff;
        }

        /* Empty state */
        .empty-slot-msg {
            color: #adb5bd;
            font-style: italic;
            text-align: center;
            padding: 0.5rem;
            font-size: 0.9rem;
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center mb-4 bg-white p-3 rounded shadow-sm gap-3">
                <div class="order-2 order-md-1 text-md-start text-center">
                    <a href="{{ route('staff.register', ['date' => \Carbon\Carbon::parse($date)->subWeek()->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-responsive">&laquo; Tuần trước</a>
                </div>
                
                <div class="text-center order-1 order-md-2 flex-grow-1 px-2">
                    <h4 class="mb-0 fw-bold text-uppercase text-primary" style="white-space: nowrap;">Đăng ký lịch làm</h4>
                    <span class="text-muted d-block mt-1" style="white-space: nowrap;">Từ {{ $weekDays[0]['date'] }} đến {{ $weekDays[6]['date'] }}</span>
                </div>

                <div class="order-3 order-md-3 text-md-end text-center">
                    <a href="{{ route('staff.register', ['date' => \Carbon\Carbon::parse($date)->addWeek()->format('Y-m-d')]) }}" class="btn btn-outline-primary btn-responsive">Tuần sau &raquo;</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-3"></i>
                        <div>
                            <strong>Đã có lỗi xảy ra:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Form đăng ký thời gian rảnh</h5>
                </div>
                <div class="card-body">
                    
                    @if(!$week)
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/2748/2748558.png" width="80" alt="No Week" class="mb-3 opacity-50">
                            <h4 class="text-muted">Tuần này chưa mở đăng ký</h4>
                            <p>Vui lòng liên hệ quản lý để mở lịch tuần này.</p>
                        </div>
                    @else
                        <form action="{{ route('staff.store') }}" method="POST" id="registerForm">
                            @csrf
                            <input type="hidden" name="week_id" value="{{ $week->WeekID }}">

                            <p class="text-muted small mb-3">
                                <i class="bi bi-info-circle"></i> 
                                Bạn có thể đăng ký <strong>nhiều khung giờ rảnh</strong> trong mỗi ngày. Nhấn <strong>"+ Thêm khung giờ"</strong> để thêm.
                            </p>

                            @foreach($weekDays as $day)
                                @php
                                    $slots = $myAvailabilities[$day['code']] ?? [];
                                    $hasSlots = count($slots) > 0;
                                @endphp

                                <div class="day-card {{ $hasSlots ? 'has-slots' : '' }}" id="card-{{ $day['code'] }}">
                                    {{-- Header ngày --}}
                                    <div class="day-card-header">
                                        <div>
                                            <span class="fw-bold">{{ $day['name'] }}</span>
                                            <small class="text-muted ms-2">{{ $day['date'] }}</small>
                                        </div>
                                        <span class="badge {{ $hasSlots ? 'bg-success' : 'bg-secondary' }}" id="badge-{{ $day['code'] }}">
                                            {{ $hasSlots ? count($slots) . ' khung giờ' : 'Chưa đăng ký' }}
                                        </span>
                                    </div>

                                    {{-- Body: Danh sách các khung giờ --}}
                                    <div class="day-card-body">
                                        <div class="slots-container" id="slots-{{ $day['code'] }}">
                                            @if($hasSlots)
                                                @foreach($slots as $index => $slot)
                                                    <div class="slot-row" data-day="{{ $day['code'] }}">
                                                        <input type="time" 
                                                               name="availability[{{ $day['code'] }}][{{ $index }}][start]" 
                                                               value="{{ substr($slot->AvailableFrom, 0, 5) }}" 
                                                               class="form-control"
                                                               placeholder="Từ">
                                                        <span class="slot-separator">→</span>
                                                        <input type="time" 
                                                               name="availability[{{ $day['code'] }}][{{ $index }}][end]" 
                                                               value="{{ substr($slot->AvailableTo, 0, 5) }}" 
                                                               class="form-control"
                                                               placeholder="Đến">
                                                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-slot" onclick="removeSlot(this)" title="Xóa khung giờ này">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="empty-slot-msg" id="empty-{{ $day['code'] }}">
                                                    Chưa có khung giờ nào — nhấn nút bên dưới để thêm
                                                </div>
                                            @endif
                                        </div>

                                        <button type="button" class="btn-add-slot mt-2" onclick="addSlot('{{ $day['code'] }}')">
                                            <i class="bi bi-plus-circle"></i> Thêm khung giờ
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            <div class="d-grid gap-2 mt-4">
                                @if(!Auth::user()->EndDate)
                                    <button type="submit" class="btn btn-primary btn-lg shadow">💾 Lưu đăng ký</button>
                                @else
                                    <div class="alert alert-danger text-center">Bạn không thể đăng ký lịch vì đã nghỉ làm.</div>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Counter để tạo index unique cho mỗi slot mới
    let slotCounters = {};

    document.addEventListener('DOMContentLoaded', function() {
        // Đếm số slot hiện có cho mỗi ngày
        const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        days.forEach(day => {
            const container = document.getElementById('slots-' + day);
            slotCounters[day] = container.querySelectorAll('.slot-row').length;
        });
    });

    function addSlot(dayCode) {
        const container = document.getElementById('slots-' + dayCode);
        
        // Xóa thông báo "chưa có khung giờ"
        const emptyMsg = document.getElementById('empty-' + dayCode);
        if (emptyMsg) emptyMsg.remove();

        // Tạo index mới
        const index = slotCounters[dayCode] || 0;
        slotCounters[dayCode] = index + 1;

        // Tạo slot row mới
        const slotRow = document.createElement('div');
        slotRow.className = 'slot-row';
        slotRow.setAttribute('data-day', dayCode);
        slotRow.innerHTML = `
            <input type="time" 
                   name="availability[${dayCode}][${index}][start]" 
                   class="form-control"
                   placeholder="Từ">
            <span class="slot-separator">→</span>
            <input type="time" 
                   name="availability[${dayCode}][${index}][end]" 
                   class="form-control"
                   placeholder="Đến">
            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-slot" onclick="removeSlot(this)" title="Xóa khung giờ này">
                <i class="bi bi-trash"></i>
            </button>
        `;

        container.appendChild(slotRow);
        updateDayStatus(dayCode);

        // Focus vào input đầu tiên của slot mới
        slotRow.querySelector('input[type="time"]').focus();
    }

    function removeSlot(btn) {
        const slotRow = btn.closest('.slot-row');
        const dayCode = slotRow.getAttribute('data-day');
        
        slotRow.remove();
        updateDayStatus(dayCode);

        // Nếu không còn slot nào, hiển thị lại thông báo trống
        const container = document.getElementById('slots-' + dayCode);
        if (container.querySelectorAll('.slot-row').length === 0) {
            const emptyMsg = document.createElement('div');
            emptyMsg.className = 'empty-slot-msg';
            emptyMsg.id = 'empty-' + dayCode;
            emptyMsg.textContent = 'Chưa có khung giờ nào — nhấn nút bên dưới để thêm';
            container.appendChild(emptyMsg);
        }
    }

    function updateDayStatus(dayCode) {
        const container = document.getElementById('slots-' + dayCode);
        const card = document.getElementById('card-' + dayCode);
        const badge = document.getElementById('badge-' + dayCode);
        const slotCount = container.querySelectorAll('.slot-row').length;

        if (slotCount > 0) {
            card.classList.add('has-slots');
            badge.className = 'badge bg-success';
            badge.textContent = slotCount + ' khung giờ';
        } else {
            card.classList.remove('has-slots');
            badge.className = 'badge bg-secondary';
            badge.textContent = 'Chưa đăng ký';
        }
    }
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
@endsection