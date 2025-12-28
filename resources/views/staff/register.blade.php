@extends('layouts.app')

@section('title', 'Đăng ký lịch làm việc')

@section('content')
<div class="container">
    {{-- 1. THÊM THƯ VIỆN FLATPICKR --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .time-picker {
            background-color: #fff !important;
            cursor: pointer;
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
                <a href="{{ route('staff.register', ['date' => \Carbon\Carbon::parse($date)->subWeek()->format('Y-m-d')]) }}" class="btn btn-outline-secondary">&laquo; Tuần trước</a>
                
                <div class="text-center">
                    <h4 class="mb-0 fw-bold text-uppercase text-primary">Đăng ký lịch làm</h4>
                    <span class="text-muted">Tuần từ {{ $weekDays[0]['date'] }} đến {{ $weekDays[6]['date'] }}</span>
                </div>

                <a href="{{ route('staff.register', ['date' => \Carbon\Carbon::parse($date)->addWeek()->format('Y-m-d')]) }}" class="btn btn-outline-primary">Tuần sau &raquo;</a>
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

                            <div class="table-responsive">
                               <table class="table table-hover align-middle" style="min-width: 600px;"> <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%">Ngày</th> <th style="width: 30%">Từ</th>
                                        <th style="width: 30%">Đến</th>
                                        <th style="width: 15%"></th>
                                    </tr>
                                </thead>
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
                                                <td>
                                                    {{-- 2. ĐỔI TYPE="TIME" THÀNH TYPE="TEXT" VÀ THÊM CLASS time-picker --}}
                                                    <input type="text" class="form-control time-picker" 
                                                        name="availability[{{ $day['code'] }}][start]" 
                                                        value="{{ $oldStart }}"
                                                        placeholder="00:00">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control time-picker" 
                                                        name="availability[{{ $day['code'] }}][end]" 
                                                        value="{{ $oldEnd }}"
                                                        placeholder="00:00">
                                                </td>
                                                <td class="text-center">
                                                    @if($isRegistered)
                                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearRow('{{ $day['code'] }}')" title="Xóa đăng ký ngày này">
                                                            <i class="bi bi-trash"></i> Xóa
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">--</span>
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
            // 1. Tìm dòng tương ứng
            let row = document.getElementById('row-' + dayCode);
            
            // 2. Xóa giá trị trong 2 ô input (Sửa selector cho đúng class)
            let inputs = row.querySelectorAll('input.time-picker');
            inputs.forEach(input => {
                input._flatpickr.clear(); 
                input.value = '';       
            });

            // 3. Đổi màu dòng về bình thường
            row.classList.remove('table-success');
            
            // 4. Ẩn nút Xóa đi
            let btn = row.querySelector('button');
            if(btn) btn.style.display = 'none';
        }
    }
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
@endsection