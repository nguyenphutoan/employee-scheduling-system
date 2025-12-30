@extends('layouts.app')

@section('title', 'Bảng lịch tuần tổng hợp')

@section('content')
<div class="container-fluid">

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
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        
        {{-- NÚT TUẦN TRƯỚC --}}
        <div>
            @if(isset($prevWeek))
                <a href="{{ route('manager.dashboard', ['week_id' => $prevWeek->WeekID]) }}" class="btn btn-outline-primary">
                    &laquo; Tuần trước
                </a>
            @else
                <button class="btn btn-outline-secondary" disabled>&laquo; Tuần trước</button>
            @endif
        </div>

        {{-- TIÊU ĐỀ & NGÀY THÁNG --}}
        <div class="text-center">
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
        <div class="d-flex gap-2">
            @if(isset($nextWeek))
                <a href="{{ route('manager.dashboard', ['week_id' => $nextWeek->WeekID]) }}" class="btn btn-outline-primary">
                    Tuần tới &raquo;
                </a>
            @else
                <button class="btn btn-outline-secondary" disabled>Tuần tới &raquo;</button>
            @endif

            <a href="{{ route('manager.export_schedule', ['week_id' => $currentWeek->WeekID ?? '']) }}" class="btn btn-success text-white">
                <i class="bi bi-file-earmark-excel"></i> Xuất Excel
            </a>

            <button class="btn btn-secondary" onclick="printSchedule()" title="In bảng này">
                <i class="bi bi-printer"></i> In Lịch
            </button>
        </div>
    </div>

    @if(isset($noData))
        <div class="alert alert-warning">Chưa có dữ liệu tuần nào.</div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle text-center mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-start ps-3" style="min-width: 150px;">Nhân viên</th>
                                
                                {{-- Loop ra 7 ngày --}}
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
                                    {{-- Cột tên nhân viên --}}
                                    <td class="text-start ps-3 align-middle">
                                        <div class="fw-bold text-dark">{{ $user->FullName }}</div>
                                        <small class="text-muted" style="font-size: 0.8rem;">
                                            Mã NV: {{ $user->UserName }}
                                        </small>
                                    </td>

                                    {{-- Loop ra 7 ô dữ liệu --}}
                                    @foreach($daysMap as $dayCode)
                                        @php
                                            $cellContent = $schedule[$user->UserID][$dayCode] ?? '';
                                        @endphp
                                        <td class="{{ empty($cellContent) ? 'bg-light' : '' }}">
                                            @if(!empty($cellContent))
                                                {!! $cellContent !!}
                                            @endif
                                        </td>
                                    @endforeach

                                    {{-- Cột Tổng giờ --}}
                                    <td class="fw-bold fs-5 text-success">
                                        {{ number_format($totalHours[$user->UserID], 1) }}
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
                                <td class="text-start ps-3 align-middle">
                                    <span class="text-uppercase">Tổng cộng</span>
                                </td>

                                {{-- Loop ra tổng giờ của từng ngày --}}
                                @foreach($daysMap as $dayCode)
                                    <td class="align-middle">
                                        @if($dailyTotals[$dayCode] > 0)
                                            <span class="fs-5">{{ number_format($dailyTotals[$dayCode], 1) }}</span>
                                        @else
                                            <span class="opacity-50">-</span>
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Ô Tổng tất cả (Góc dưới cùng bên phải) --}}
                                <td class="bg-success text-white align-middle fs-4">
                                    {{ number_format($grandTotal, 1) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- CHÚ THÍCH TRẠNG THÁI --}}
                    <div class="mt-4 p-3 bg-white rounded shadow-sm no-print">
                        <h6 class="fw-bold">📌 Chú thích trạng thái:</h6>
                        <div class="d-flex gap-4">
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
        /* 1. Ẩn tất cả các thành phần không cần thiết */
        .sidebar, .navbar, .no-print, footer, .alert, .btn {
            display: none !important;
        }

        /* 2. Cấu hình trang in */
        @page {
            size: A4 landscape; 
            margin: 10mm;       
        }

        body {
            background: white;
            font-family: 'Times New Roman', serif; 
        }

        .container-fluid, .card, .card-body {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* 4. Co nhỏ bảng để vừa trang giấy (Scale) */
        table {
            width: 100% !important;
            font-size: 12px; /* Chữ nhỏ lại một chút để vừa hàng */
        }

        /* Đảm bảo in được màu nền (header, footer bảng) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<script>
    function printSchedule() {
        // 1. Lấy ngày tháng để đặt tên
        @if(isset($currentWeek))
            var startDate = "{{ date('d-m', strtotime($currentWeek->StartDate)) }}";
            var endDate = "{{ date('d-m-y', strtotime($currentWeek->EndDate)) }}";
            var fileName = `LichTuan_${startDate}_${endDate}`;
        @else
            var fileName = "LichTuan_Chung";
        @endif

        // 2. Lưu tiêu đề cũ
        var oldTitle = document.title;

        // 3. Đổi tiêu đề trang (Trình duyệt sẽ dùng cái này làm tên file PDF)
        document.title = fileName;

        // 4. Gọi lệnh in
        window.print();

        // 5. Trả lại tiêu đề cũ
        setTimeout(() => {
            document.title = oldTitle;
        }, 1000);
    }
</script>

@endsection