@extends('layouts.app')

@section('title', 'Lịch làm việc của tôi')

@section('content')
<div class="container-fluid">

    @if(isset($noData))
        <div class="alert alert-warning text-center">Chưa có dữ liệu lịch làm việc.</div>
    @else

        {{-- 1. HEADER & ĐIỀU HƯỚNG --}}
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            {{-- Nút tuần trước --}}
            @if($prevWeek)
                <a href="{{ route('staff.dashboard', ['week_id' => $prevWeek->WeekID]) }}" class="btn btn-outline-primary">
                    &laquo; Tuần trước
                </a>
            @else
                <button class="btn btn-outline-secondary" disabled>&laquo;</button>
            @endif

            {{-- Tiêu đề giữa --}}
            <div class="text-center">
                <h4 class="mb-0 fw-bold text-primary text-uppercase">
                    <i class="bi bi-calendar-check-fill me-2"></i> Lịch làm của tôi
                </h4>
                <div class="text-muted fw-bold mt-1">
                    {{ date('d/m', strtotime($currentWeek->StartDate)) }} 
                    - 
                    {{ date('d/m', strtotime($currentWeek->EndDate)) }}
                </div>
            </div>

            {{-- Nút tuần sau --}}
            <div class="d-flex gap-2">
                @if($nextWeek)
                    <a href="{{ route('staff.dashboard', ['week_id' => $nextWeek->WeekID]) }}" class="btn btn-outline-primary">
                        Tuần tới &raquo;
                    </a>
                @else
                    <button class="btn btn-outline-secondary" disabled>&raquo;</button>
                @endif
                
                {{-- Nút đăng ký lịch (Giữ lại nút cũ của bạn) --}}
                <a href="register" class="btn btn-success">
                    <i class="bi bi-pencil-square"></i> Đăng ký
                </a>
            </div>
        </div>

        {{-- 2. BẢNG LỊCH (Responsive: Trên mobile sẽ cuộn ngang) --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-top text-center mb-0" style="min-width: 800px;">
                        {{-- TIÊU ĐỀ CỘT: THỨ / NGÀY --}}
                        <thead class="bg-light text-secondary">
                            <tr>
                                @foreach($daysMap as $dayCode)
                                    <th style="width: 14.28%;">
                                        <div class="text-uppercase small fw-bold">{{ $weekDates[$dayCode]['name_vn'] }}</div>
                                        <div class="fs-5 text-dark">{{ $weekDates[$dayCode]['date'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        {{-- NỘI DUNG LỊCH --}}
                        <tbody>
                            <tr>
                                @foreach($daysMap as $dayCode)
                                    <td class="p-2" style="height: 150px; background-color: #f8f9fa;">
                                        @if(!empty($mySchedule[$dayCode]))
                                            @foreach($mySchedule[$dayCode] as $shift)
                                                @php
                                                    // Xác định màu nền dựa trên trạng thái
                                                    $statusColor = 'bg-primary'; // Mặc định Submitted (Xanh dương)
                                                    if($shift['status'] == 'StaffApproved') $statusColor = 'bg-success'; // Xanh lá
                                                    if($shift['status'] == 'Approved') $statusColor = 'bg-danger'; // Đỏ

                                                    // Kiểm tra điều kiện hiện nút Tick:
                                                    // 1. Phải là Submitted
                                                    // 2. Ngày hiện tại phải lớn hơn ngày cuối tuần của lịch này (Tức là tuần đã qua)
                                                    $isPastWeek = \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($currentWeek->EndDate));
                                                    $canConfirm = ($shift['status'] == 'Submitted') && $isPastWeek;
                                                @endphp

                                                <div class="card border-0 shadow-sm mb-2 text-white {{ $statusColor }}">
                                                    <div class="card-body p-2 text-start">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div>
                                                                <div class="fw-bold fs-5">
                                                                    {{ $shift['start'] }} - {{ $shift['end'] }}
                                                                </div>
                                                                <div class="fw-bold small">
                                                                    <i class="bi bi-person-workspace"></i> {{ $shift['position'] }}
                                                                </div>
                                                                <div class="small mt-1 opacity-75">
                                                                    <i class="bi bi-clock"></i> {{ $shift['hours'] }} giờ
                                                                </div>
                                                            </div>

                                                            {{-- Nút Tick xác nhận --}}
                                                            @if($canConfirm)
                                                                <form action="{{ route('staff.confirm_assignment', $shift['id']) }}" method="POST">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-light btn-sm text-primary shadow-sm" title="Xác nhận đã làm">
                                                                        <i class="bi bi-check-lg fw-bold"></i>
                                                                    </button>
                                                                </form>
                                                            @elseif($shift['status'] == 'StaffApproved')
                                                                <i class="bi bi-check-circle-fill fs-4 text-white" title="Đã xác nhận"></i>
                                                            @elseif($shift['status'] == 'Approved')
                                                                <i class="bi bi-check-all fs-4 text-white" title="Quản lý đã duyệt"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            {{-- Ngày nghỉ --}}
                                            <div class="text-muted opacity-25 mt-4">
                                                <i class="bi bi-cup-hot fs-1"></i>
                                                <div class="small">Nghỉ</div>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                        
                        {{-- FOOTER: TỔNG GIỜ --}}
                        <tfoot>
                            <tr>
                                <td colspan="7" class="text-end bg-white p-3">
                                    <span class="text-muted me-2">Tổng giờ làm việc tuần này:</span>
                                    <span class="fw-bold fs-4 text-success">{{ $totalHours }} giờ</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    <div class="mt-4 p-3 bg-white rounded shadow-sm">
                        <h6 class="fw-bold">📌 Chú thích trạng thái:</h6>
                        <div class="d-flex gap-4">
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-primary" style="width: 20px; height: 20px;"></div>
                                <small>Đã chốt lịch (Submitted)</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-success" style="width: 20px; height: 20px;"></div>
                                <small>Nhân viên đã xác nhận (StaffApproved)</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-danger" style="width: 20px; height: 20px;"></div>
                                <small>Quản lý đã duyệt (Approved)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>
@endsection