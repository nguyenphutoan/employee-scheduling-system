@extends('layouts.app')

@section('title', 'Đăng ký lịch làm việc')

@section('content')
<div class="container">
    {{-- 1. THÊM THƯ VIỆN FLATPICKR VÀ CUSTOM CSS RESPONSIVE --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .time-picker {
            background-color: #fff !important;
            cursor: pointer;
        }

        /* Tùy chỉnh chiều rộng nút bấm cho responsive */
        .btn-responsive { width: 100%; }
        @media (min-width: 768px) {
            .btn-responsive { width: auto; }
        }

        /* --- CSS BIẾN BẢNG THÀNH DẠNG CARD TRÊN MOBILE --- */
        @media (max-width: 767px) {
            .table-mobile-responsive thead { 
                display: none; 
            }
            .table-mobile-responsive tbody, 
            .table-mobile-responsive tr, 
            .table-mobile-responsive td { 
                display: block; 
                width: 100%; 
            }
            .table-mobile-responsive tr { 
                margin-bottom: 1rem; 
                border: 1px solid #dee2e6; 
                border-radius: 0.5rem; 
                padding: 0.5rem; 
                background-color: #fff;
                box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            }
            .table-mobile-responsive tr.table-success {
                background-color: #d1e7dd !important;
                border-color: #badbcc;
            }
            .table-mobile-responsive td { 
                display: flex; 
                align-items: center; 
                justify-content: space-between; 
                border: none; 
                padding: 0.5rem;
            }
            /* Gắn nhãn cho các ô trên mobile */
            .table-mobile-responsive td::before { 
                content: attr(data-label); 
                font-weight: bold; 
                width: 25%; 
                color: #495057;
            }
            /* Ô Ngày/Tháng (Header của Card) */
            .table-mobile-responsive td:first-child { 
                justify-content: center; 
                text-align: center; 
                background-color: #f8f9fa; 
                border-radius: 0.5rem; 
                margin-bottom: 0.5rem;
                border-bottom: 1px solid #dee2e6;
                padding-bottom: 0.75rem;
            }
            .table-mobile-responsive tr.table-success td:first-child {
                background-color: #c3ebd7;
            }
            .table-mobile-responsive td:first-child::before { display: none; }
            .table-mobile-responsive td:first-child span { font-size: 1.1rem; }
            
            /* Ô Input và Nút xóa */
            .table-mobile-responsive td input { width: 70%; }
            .table-mobile-responsive td:last-child { justify-content: flex-end; padding-top: 0; }
            .table-mobile-responsive td:last-child::before { display: none; }
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
                        <form action="{{ route('staff.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="week_id" value="{{ $week->WeekID }}">

                            {{-- Bỏ min-width và thêm class table-mobile-responsive --}}
                            <div class="table-responsive border-0">
                               <table class="table table-hover align-middle table-mobile-responsive"> 
                                   <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%">Ngày</th> 
                                            <th style="width: 30%">Từ</th>
                                            <th style="width: 30%">Đến</th>
                                            <th style="width: 15%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($weekDays as $day)
                                            @php
                                                $oldStart = $myAvailabilities[$day['code']]->AvailableFrom ?? '';
                                                $oldEnd = $myAvailabilities[$day['code']]->AvailableTo ?? '';
                                                if($oldStart) $oldStart = substr($oldStart, 0, 5);
                                                if($oldEnd) $oldEnd = substr($oldEnd, 0, 5);
                                                $isRegistered = !empty($oldStart);
                                            @endphp
                                            
                                            <tr id="row-{{ $day['code'] }}" class="{{ $isRegistered ? 'table-success' : '' }}">
                                                <td>
                                                    <span class="fw-bold">{{ $day['name'] }}</span><br>
                                                    <small class="text-muted">{{ $day['date'] }}</small>
                                                </td>
                                                {{-- Thêm data-label để hiển thị chữ trên Mobile --}}
                                                <td data-label="Từ:">
                                                    <input type="text" class="form-control time-picker" 
                                                        name="availability[{{ $day['code'] }}][start]" 
                                                        value="{{ $oldStart }}"
                                                        placeholder="00:00">
                                                </td>
                                                <td data-label="Đến:">
                                                    <input type="text" class="form-control time-picker" 
                                                        name="availability[{{ $day['code'] }}][end]" 
                                                        value="{{ $oldEnd }}"
                                                        placeholder="00:00">
                                                </td>
                                                <td class="text-center">
                                                    @if($isRegistered)
                                                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="clearRow('{{ $day['code'] }}')" title="Xóa đăng ký ngày này">
                                                            <i class="bi bi-trash"></i> Xóa
                                                        </button>
                                                    @else
                                                        <span class="text-muted small d-none d-md-inline">--</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

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
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".time-picker", {
            enableTime: true,       
            noCalendar: true,       
            dateFormat: "H:i",      // Định dạng 24h (Giờ:Phút)
            time_24hr: true,        
            allowInput: true        
        });
    });

    function clearRow(dayCode) {
        if(confirm('Bạn muốn hủy đăng ký ngày này? (Nhớ bấm LƯU để áp dụng)')) {
            let row = document.getElementById('row-' + dayCode);
            
            let inputs = row.querySelectorAll('input.time-picker');
            inputs.forEach(input => {
                input._flatpickr.clear(); 
                input.value = '';       
            });

            row.classList.remove('table-success');
            
            let btn = row.querySelector('button');
            if(btn) btn.style.display = 'none';
        }
    }
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
@endsection