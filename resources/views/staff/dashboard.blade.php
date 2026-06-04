@extends('layouts.app')

@section('title', 'Lịch làm việc của tôi')

@section('content')
<div class="container-fluid">

    {{-- CSS RESPONSIVE CHO GIAO DIỆN LỊCH --}}
    <style>
        /* Nút full width trên mobile */
        .btn-responsive { width: 100%; }
        @media (min-width: 768px) {
            .btn-responsive { width: auto; }
            .table-calendar { min-width: 800px; } /* Giữ độ rộng tối thiểu trên PC */
            .desktop-cell { height: 150px; background-color: #f8f9fa; }
        }

        /* Biến bảng thành danh sách dạng Card trên Mobile */
        @media (max-width: 767px) {
            .table-calendar thead { display: none; } /* Ẩn tiêu đề ngang */
            .table-calendar tbody, 
            .table-calendar tfoot, 
            .table-calendar tr, 
            .table-calendar td {
                display: block;
                width: 100%;
            }
            .table-calendar td {
                height: auto !important; /* Hủy fixed height 150px */
                border: none;
                border-bottom: 5px solid #e9ecef; /* Tạo dải ngăn cách giữa các ngày */
                padding: 1rem !important;
                background-color: #fff !important;
                text-align: left !important;
            }
            .table-calendar tfoot td {
                border-bottom: none;
                text-align: center !important;
                background-color: #f8f9fa !important;
            }
        }
    </style>

    @if(isset($noData))
        <div class="alert alert-warning text-center">Chưa có dữ liệu lịch làm việc.</div>
    @else

        {{-- 1. HEADER & ĐIỀU HƯỚNG (Căn chỉnh lại Flexbox cho Mobile) --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm gap-3">
            {{-- Nút tuần trước --}}
            <div class="order-2 order-md-1 w-100 text-md-start">
                @if($prevWeek)
                    <a href="{{ route('staff.dashboard', ['week_id' => $prevWeek->WeekID]) }}" class="btn btn-outline-primary btn-responsive">
                        &laquo; Tuần trước
                    </a>
                @else
                    <button class="btn btn-outline-secondary btn-responsive" disabled>&laquo; Tuần trước</button>
                @endif
            </div>

            {{-- Tiêu đề giữa --}}
            <div class="text-center order-1 order-md-2">
                <h4 class="mb-0 fw-bold text-primary text-uppercase">
                    <i class="bi bi-calendar-check-fill me-2"></i> Lịch làm của tôi
                </h4>
                <div class="text-muted fw-bold mt-1">
                    {{ date('d/m', strtotime($currentWeek->StartDate)) }} 
                    - 
                    {{ date('d/m', strtotime($currentWeek->EndDate)) }}
                </div>
            </div>

            {{-- Nút tuần sau & Đăng ký --}}
            <div class="order-3 order-md-3 w-100 text-md-end d-flex flex-column flex-md-row gap-2 justify-content-end">
                <a href="register" class="btn btn-success btn-responsive">
                    <i class="bi bi-pencil-square"></i> Đăng ký
                </a>
                
                @if($nextWeek)
                    <a href="{{ route('staff.dashboard', ['week_id' => $nextWeek->WeekID]) }}" class="btn btn-outline-primary btn-responsive">
                        Tuần tới &raquo;
                    </a>
                @else
                    <button class="btn btn-outline-secondary btn-responsive" disabled>Tuần tới &raquo;</button>
                @endif
            </div>
        </div>

        {{-- 2. BẢNG LỊCH --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                {{-- Bỏ min-width inline, thay bằng class table-calendar --}}
                <div class="table-responsive border-0">
                    <table class="table table-bordered align-top text-center mb-0 table-calendar">
                        {{-- TIÊU ĐỀ CỘT: THỨ / NGÀY (Chỉ hiện trên PC) --}}
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
                                    {{-- Thêm class desktop-cell để xử lý background và height trên PC --}}
                                    <td class="p-2 desktop-cell">
                                        
                                        {{-- HEADER NGÀY (Chỉ hiện trên Mobile) --}}
                                        <div class="d-md-none d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                            <span class="fw-bold text-uppercase text-primary">{{ $weekDates[$dayCode]['name_vn'] }}</span>
                                            <span class="badge bg-secondary fs-6">{{ $weekDates[$dayCode]['date'] }}</span>
                                        </div>

                                        @if(!empty($mySchedule[$dayCode]))
                                            @foreach($mySchedule[$dayCode] as $shift)
                                                @php
                                                    $statusColor = 'bg-primary'; 
                                                    if($shift['status'] == 'StaffApproved') $statusColor = 'bg-success'; 
                                                    if($shift['status'] == 'Approved') $statusColor = 'bg-danger'; 

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
                                            {{-- Ngày nghỉ: Đổi style một chút để hợp cả Mobile lẫn PC --}}
                                            <div class="text-muted opacity-50 text-center py-2">
                                                <i class="bi bi-cup-hot fs-2"></i>
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
                                {{-- Thêm class text-md-end để căn phải trên PC, căn giữa trên Mobile --}}
                                <td colspan="7" class="text-center text-md-end bg-white p-3">
                                    <span class="text-muted me-2">Tổng giờ làm việc tuần này:</span>
                                    <span class="fw-bold fs-4 text-success">{{ $totalHours }} giờ</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    {{-- CHÚ THÍCH TRẠNG THÁI --}}
                    <div class="mt-4 p-3 bg-white rounded shadow-sm">
                        <h6 class="fw-bold">📌 Chú thích trạng thái:</h6>
                        {{-- Đổi flex-wrap để tự động rớt dòng nếu màn hình nhỏ --}}
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-primary" style="width: 20px; height: 20px;"></div>
                                <small>Đã chốt (Submitted)</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-success" style="width: 20px; height: 20px;"></div>
                                <small>Đã xác nhận (StaffApproved)</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-danger" style="width: 20px; height: 20px;"></div>
                                <small>Quản lý duyệt (Approved)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endif
</div>
@endsection