@extends('layouts.app')

@section('title', 'Bảng lương toàn nhân viên')

@section('content')
<div class="container-fluid">
    
    {{-- CSS RESPONSIVE CHO BẢNG LƯƠNG MANAGER --}}
    <style>
        .btn-responsive { width: 100%; }
        @media (min-width: 768px) { .btn-responsive { width: auto; } }

        @media (max-width: 767px) {
            .table-mobile-responsive thead { display: none; }
            .table-mobile-responsive tbody, .table-mobile-responsive tr, .table-mobile-responsive td { display: block; width: 100%; }
            
            .table-mobile-responsive tr { 
                margin-bottom: 1.5rem; 
                border: 1px solid #dee2e6; 
                border-radius: 0.75rem; 
                background-color: #fff; 
                box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.05); 
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
                font-weight: bold; color: #495057; text-align: left;
            }
            
            /* Thẻ Header Nhân Viên */
            .table-mobile-responsive td:first-child { 
                background-color: #f8f9fa; border-radius: 0.75rem 0.75rem 0 0; border-bottom: 2px solid #dee2e6; justify-content: center; 
            }
            .table-mobile-responsive td:first-child::before { display: none; }
            .table-mobile-responsive td:first-child .d-flex { flex-direction: column; text-align: center; width: 100%; }
            .table-mobile-responsive td:first-child .avatar-circle { margin: 0 auto 0.5rem auto !important; width: 50px !important; height: 50px !important; font-size: 1.25rem; }
        }
    </style>

    {{-- 1. BỘ LỌC VÀ HEADER --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3 bg-white p-3 rounded shadow-sm">
        <h4 class="mb-0 fw-bold text-primary text-uppercase text-center text-md-start w-100">
            <i class="bi bi-cash-stack me-2"></i> Bảng lương tháng {{ $month }}/{{ $year }}
        </h4>
        
        <form action="{{ route('manager.payroll') }}" method="GET" class="d-flex flex-wrap flex-md-nowrap gap-2 w-100 justify-content-center justify-content-md-end">
            <select name="month" class="form-select form-select-sm" style="min-width: 110px;">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng {{ $m }}</option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm" style="min-width: 100px;">
                @for($y=date('Y'); $y>=2023; $y--)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">Lọc</button>
        </form>
    </div>

    {{-- 2. THỐNG KÊ TỔNG QUAN (CARDS) --}}
    <div class="row g-3 mb-4">
        {{-- Tổng tiền --}}
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-uppercase opacity-75 fw-bold">Tổng quỹ lương (Ước tính)</div>
                            <div class="display-6 fw-bold mt-2">
                                {{ number_format($grandTotalSalary, 0, ',', '.') }} <span class="fs-5">VND</span>
                            </div>
                        </div>
                        <i class="bi bi-wallet2 fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tổng giờ --}}
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm bg-white text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-uppercase text-muted fw-bold">Tổng giờ làm việc</div>
                            <div class="display-6 fw-bold mt-2 text-success">
                                {{ number_format($grandTotalHours, 1) }} <span class="fs-5">giờ</span>
                            </div>
                        </div>
                        <i class="bi bi-clock-history fs-1 text-muted opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thông tin chu kỳ --}}
        <div class="col-md-12 col-xl-4">
             <div class="card border-0 shadow-sm bg-light h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-calendar-range me-2 text-primary"></i>
                        <strong>Chu kỳ tính lương:</strong>
                    </div>
                    <div class="text-muted">
                        Từ ngày <span class="fw-bold text-dark">{{ $startDate->format('d/m/Y') }}</span> 
                        đến <span class="fw-bold text-dark">{{ $endDate->format('d/m/Y') }}</span>
                    </div>
                    <small class="text-danger mt-2 fst-italic">
                        * Mức lương: <strong>FOH 26k/h</strong> - <strong>BOH 28k/h</strong>.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DANH SÁCH CHI TIẾT --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-people text-primary"></i> Chi tiết nhân viên</h5>
        </div>
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive border-0">
                <table class="table table-hover align-middle mb-0 table-mobile-responsive">
                    <thead class="bg-light text-secondary small text-uppercase">
                        <tr>
                            <th class="ps-4">Nhân viên</th>
                            <th class="text-center">Tổng giờ</th>
                            <th class="text-center">Giờ đêm (>22h)</th>
                            <th class="text-end pe-4">Tạm tính (VND)</th>
                            <th class="text-end pe-4">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payrollData as $data)
                            <tr>
                                <td data-label="Nhân viên" class="ps-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 40px; height: 40px;">
                                            {{ substr($data->user->FullName, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-5 fs-md-6">{{ $data->user->FullName }}</div>
                                            <div class="small text-muted">{{ $data->user->UserName }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Tổng giờ" class="text-md-center fw-bold">{{ number_format($data->total_hours, 1) }}h</td>
                                <td data-label="Giờ đêm" class="text-md-center text-muted">{{ number_format($data->night_hours, 1) }}h</td>
                                <td data-label="Tạm tính" class="text-md-end pe-md-4">
                                    <span class="badge bg-success fs-6 fw-bold p-2">
                                        {{ number_format($data->salary, 0, ',', '.') }} đ
                                    </span>
                                </td>
                                <td data-label="Chi tiết" class="text-md-end pe-md-4">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-4 w-100 w-md-auto" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailModal-{{ $data->user->UserID }}">
                                        Xem <i class="bi bi-eye-fill ms-1"></i>
                                    </button>

                                    {{-- MODAL CHI TIẾT (Áp dụng responsive bên trong) --}}
                                    <div class="modal fade" id="detailModal-{{ $data->user->UserID }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title fw-bold text-primary">Chi tiết công: {{ $data->user->FullName }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-3">
                                                    <div class="table-responsive border-0">
                                                        <table class="table table-striped align-middle mb-0 text-start table-mobile-responsive">
                                                            <thead class="table-dark">
                                                                <tr>
                                                                    <th class="ps-3">Ngày</th>
                                                                    <th>Ca làm</th>
                                                                    <th>Vị trí</th>
                                                                    <th>Đơn giá</th>
                                                                    <th class="text-center">Giờ làm</th>
                                                                    <th class="text-center">Đêm</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($data->details as $item)
                                                                    <tr>
                                                                        <td data-label="Ngày" class="ps-md-3 fw-bold">{{ $item['date'] }}</td>
                                                                        <td data-label="Thời gian">{{ $item['time'] }}</td>
                                                                        <td data-label="Vị trí">{{ $item['position'] }}</td>
                                                                        <td data-label="Đơn giá">
                                                                            @if(isset($item['rate']))
                                                                                <span class="badge bg-light text-dark border">{{ number_format($item['rate']) }}</span>
                                                                            @else
                                                                                <span class="text-muted">-</span>
                                                                            @endif
                                                                        </td>
                                                                        <td data-label="Số giờ" class="text-md-center fw-bold text-primary">{{ number_format($item['hours'], 1) }}h</td>
                                                                        <td data-label="Đêm" class="text-md-center {{ $item['night'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                                            {{ number_format($item['night'], 1) }}h
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                                @if(count($data->details) == 0)
                                                                    <tr><td colspan="6" class="text-center text-muted py-4">Không có dữ liệu approved</td></tr>
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light d-flex flex-column flex-md-row justify-content-between align-items-center">
                                                    <div class="small text-muted mb-2 mb-md-0 text-center text-md-start">
                                                        Tổng: <b class="text-dark">{{ number_format($data->total_hours, 1) }}h</b> 
                                                        (Đêm: {{ number_format($data->night_hours, 1) }}h)
                                                    </div>
                                                    <div class="fw-bold text-success fs-4">
                                                        {{ number_format($data->salary, 0, ',', '.') }} đ
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- KẾT THÚC MODAL --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    Không tìm thấy dữ liệu chấm công đã duyệt trong tháng này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        {{-- 4. FOOTER: GHI CHÚ MIỄN TRỪ TRÁCH NHIỆM --}}
        <div class="card-footer bg-white p-3">
             <div class="alert alert-warning mb-0 border-0 bg-warning bg-opacity-10 small">
                <div class="d-flex flex-column flex-md-row align-items-md-center">
                    <i class="bi bi-exclamation-triangle-fill mb-2 mb-md-0 me-md-3 fs-3 text-warning"></i>
                    <div>
                        <strong>Lưu ý quan trọng:</strong> <br>
                        1. Mức lương trên được tính theo đơn giá cơ bản từng vị trí (FOH: 26k, BOH: 28k) và phụ cấp đêm 30%. <br>
                        2. Đây <b>CHƯA PHẢI LƯƠNG THỰC NHẬN CHÍNH THỨC</b>. Số liệu này chưa bao gồm: Phụ cấp giao hàng, thưởng, và chưa trừ BHXH, Đồng phục, Thuế... <br>
                        3. Bảng lương chính thức sẽ do bộ phận <b>Kế toán</b> quyết định.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection