@extends('layouts.app')

@section('title', 'Bảng lịch tuần tổng hợp')

@section('content')
<div class="container-fluid">

    {{-- CSS RESPONSIVE BẢNG LỊCH MANAGER --}}
    <style>
        .btn-responsive { width: 100%; }
        @media (min-width: 768px) {
            .btn-responsive { width: auto; }
        }

        @media (max-width: 767px) {
            .table-mobile-responsive thead { display: none; }
            .table-mobile-responsive tbody, 
            .table-mobile-responsive tr, 
            .table-mobile-responsive td, 
            .table-mobile-responsive tfoot { display: block; width: 100%; }
            
            .table-mobile-responsive tr { 
                margin-bottom: 1.5rem; 
                border: 1px solid #dee2e6; 
                border-radius: 0.5rem; 
                background-color: #fff; 
                box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05); 
                overflow: hidden;
            }
            .table-mobile-responsive td { 
                display: flex; 
                align-items: center; 
                justify-content: space-between; 
                border: none; 
                padding: 0.75rem 1rem; 
                border-bottom: 1px dashed #e9ecef; 
                text-align: right; 
            }
            .table-mobile-responsive td:last-child { border-bottom: none; }
            .table-mobile-responsive td::before { 
                content: attr(data-label); 
                font-weight: bold; 
                color: #495057; 
                text-align: left; 
                margin-right: 1rem; 
                flex-shrink: 0; 
                width: 35%;
            }
            
            /* Tiêu đề card (Tên nhân viên) */
            .table-mobile-responsive td:first-child { 
                flex-direction: column; 
                align-items: center; 
                background-color: #e9ecef; 
                border-bottom: 2px solid #dee2e6; 
            }
            .table-mobile-responsive td:first-child::before { display: none; }
            .table-mobile-responsive td:first-child .text-start { text-align: center !important; padding-left: 0 !important; }
            
            /* Dòng Footer (Tổng cộng) */
            .table-mobile-responsive tfoot tr { border-color: #198754; }
            .table-mobile-responsive tfoot td { background-color: #f8f9fa; color: #212529; }
            .table-mobile-responsive tfoot td:first-child { background-color: #198754; color: white; }
            .table-mobile-responsive tfoot td:last-child { background-color: #d1e7dd; color: #0f5132; font-size: 1.25rem; font-weight: bold; }
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
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

    {{-- 2. HEADER & ĐIỀU HƯỚNG --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm gap-3">
        
        {{-- NÚT TUẦN TRƯỚC --}}
        <div class="order-2 order-lg-1 w-100 text-lg-start">
            @if(isset($prevWeek))
                <a href="{{ route('manager.dashboard', ['week_id' => $prevWeek->WeekID]) }}" class="btn btn-outline-primary btn-responsive">
                    &laquo; Tuần trước
                </a>
            @else
                <button class="btn btn-outline-secondary btn-responsive" disabled>&laquo; Tuần trước</button>
            @endif
        </div>

        {{-- TIÊU ĐỀ & NGÀY THÁNG --}}
        <div class="text-center order-1 order-lg-2">
            <h4 class="mb-1 fw-bold text-uppercase text-primary">
                <i class="bi bi-table"></i> Bảng tổng hợp lịch tuần
            </h4>
            @if(isset($currentWeek))
                <span class="text-muted fw-bold">
                    {{ date('d/m/Y', strtotime($currentWeek->StartDate)) }} 
                    - 
                    {{ date('d/m/Y', strtotime($currentWeek->EndDate)) }}
                </span>
            @endif
        </div>
        
        {{-- NÚT TUẦN SAU & IN LỊCH --}}
        <div class="d-flex flex-column flex-md-row gap-2 order-3 order-lg-3 w-100 justify-content-lg-end">
            @if(isset($nextWeek))
                <a href="{{ route('manager.dashboard', ['week_id' => $nextWeek->WeekID]) }}" class="btn btn-outline-primary btn-responsive">
                    Tuần tới &raquo;
                </a>
            @else
                <button class="btn btn-outline-secondary btn-responsive" disabled>Tuần tới &raquo;</button>
            @endif

            <a href="{{ route('manager.export_schedule', ['week_id' => $currentWeek->WeekID ?? '']) }}" class="btn btn-success text-white btn-responsive">
                <i class="bi bi-file-earmark-excel"></i> Xuất Excel
            </a>

            <button class="btn btn-secondary btn-responsive" onclick="printSchedule()" title="In bảng này">
                <i class="bi bi-printer"></i> In Lịch
            </button>
        </div>
    </div>

    @if(isset($noData))
        <div class="alert alert-warning">Chưa có dữ liệu tuần nào.</div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body p-0 p-md-3">
                <div class="table-responsive border-0">
                    <table class="table table-bordered table-striped table-hover align-middle text-center mb-0 table-mobile-responsive">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-start ps-3" style="min-width: 150px;">Nhân viên</th>
                                @foreach($daysMap as $dayCode)
                                    <th style="min-width: 100px;">
                                        {{ $weekDates[$dayCode]['name'] }} <br>
                                        <small class="fw-normal">{{ $weekDates[$dayCode]['date'] }}</small>
                                    </th>
                                @endforeach
                                <th class="bg-success text-white" style="min-width: 100px;">Tổng giờ công</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td data-label="Nhân viên" class="text-start ps-md-3 align-middle">
                                        <div class="fw-bold text-dark fs-5">{{ $user->FullName }}</div>
                                        <small class="text-muted" style="font-size: 0.8rem;">
                                            Mã NV: {{ $user->UserName }}
                                        </small>
                                    </td>

                                    @foreach($daysMap as $dayCode)
                                        @php
                                            $cellContent = $schedule[$user->UserID][$dayCode] ?? '';
                                        @endphp
                                        <td data-label="{{ $weekDates[$dayCode]['name'] }} ({{ $weekDates[$dayCode]['date'] }})" class="{{ empty($cellContent) ? 'bg-light' : '' }}">
                                            @if(!empty($cellContent))
                                                {!! $cellContent !!}
                                            @else
                                                <span class="d-md-none text-muted small">--</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td data-label="TỔNG GIỜ LÀM" class="fw-bold fs-5 text-success">
                                        {{ number_format($totalHours[$user->UserID], 1) }}h
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">Không có nhân viên nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-secondary text-white fw-bold border-top border-3">
                            <tr>
                                <td data-label="Thống kê" class="text-start ps-3 align-middle">
                                    <span class="text-uppercase">Tổng cộng</span>
                                </td>

                                @foreach($daysMap as $dayCode)
                                    <td data-label="{{ $weekDates[$dayCode]['name'] }}" class="align-middle">
                                        @if($dailyTotals[$dayCode] > 0)
                                            <span class="fs-5">{{ number_format($dailyTotals[$dayCode], 1) }}h</span>
                                        @else
                                            <span class="opacity-50 d-none d-md-inline">-</span>
                                            <span class="d-md-none text-muted">0h</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td data-label="TỔNG TOÀN BỘ" class="bg-success text-white align-middle fs-4">
                                    {{ number_format($grandTotal, 1) }}h
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- CHÚ THÍCH TRẠNG THÁI --}}
                    <div class="mt-4 p-3 bg-white rounded shadow-sm no-print">
                        <h6 class="fw-bold">📌 Chú thích trạng thái:</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-secondary" style="width: 20px; height: 20px;"></div>
                                <small>Nháp (Draft)</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-primary" style="width: 20px; height: 20px;"></div>
                                <small>Đã gửi (Submitted)</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-success" style="width: 20px; height: 20px;"></div>
                                <small>NV Xác nhận (StaffApproved)</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="rounded me-2 bg-danger" style="width: 20px; height: 20px;"></div>
                                <small>Đã duyệt (Approved)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    @media print {
        .sidebar, .navbar, .no-print, footer, .alert, .btn { display: none !important; }
        @page { size: A4 landscape; margin: 10mm; }
        body { background: white; font-family: 'Times New Roman', serif; }
        .container-fluid, .card, .card-body { width: 100% !important; margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; }
        table { width: 100% !important; font-size: 12px; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>

<script>
    function printSchedule() {
        @if(isset($currentWeek))
            var startDate = "{{ date('d-m', strtotime($currentWeek->StartDate)) }}";
            var endDate = "{{ date('d-m-y', strtotime($currentWeek->EndDate)) }}";
            var fileName = `LichTuan_${startDate}_${endDate}`;
        @else
            var fileName = "LichTuan_Chung";
        @endif
        var oldTitle = document.title;
        document.title = fileName;
        window.print();
        setTimeout(() => { document.title = oldTitle; }, 1000);
    }
</script>

@endsection