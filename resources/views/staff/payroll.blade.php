@extends('layouts.app')

@section('title', 'Bảng lương cá nhân')

@section('content')
<div class="container">

    {{-- CSS RESPONSIVE ĐỘC QUYỀN CHO BẢNG LƯƠNG --}}
    <style>
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
            .table-mobile-responsive td { 
                display: flex; 
                align-items: center; 
                justify-content: space-between; 
                border: none; 
                padding: 0.5rem;
                border-bottom: 1px dashed #e9ecef;
            }
            .table-mobile-responsive td:last-child {
                border-bottom: none;
            }
            /* Gắn nhãn (data-label) cho các ô trên mobile */
            .table-mobile-responsive td::before { 
                content: attr(data-label); 
                font-weight: bold; 
                color: #495057;
            }
            /* Ô Ngày (Header của Biên lai) */
            .table-mobile-responsive td:first-child { 
                justify-content: center; 
                flex-direction: column;
                text-align: center; 
                background-color: #f8f9fa; 
                border-radius: 0.5rem; 
                margin-bottom: 0.5rem;
                border-bottom: 2px solid #dee2e6;
                padding-bottom: 0.75rem;
            }
            .table-mobile-responsive td:first-child::before { display: none; }
            .table-mobile-responsive td:first-child .fw-bold { font-size: 1.2rem; }
            
            /* Điều chỉnh các ô dữ liệu căn lề phải trên mobile */
            .table-mobile-responsive td > *:not(::before) {
                text-align: right;
            }
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-md-10">
            
            {{-- 1. HEADER & FILTER (Chỉnh Flexbox để ôm form trên Mobile) --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h4 class="mb-0 fw-bold text-primary text-uppercase text-center text-md-start">
                    <i class="bi bi-wallet2 me-2"></i> Bảng lương của tôi
                </h4>
                
                <form action="{{ route('staff.payroll') }}" method="GET" class="d-flex gap-2 justify-content-center">
                    <select name="month" class="form-select form-select-sm" style="min-width: 100px;">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                    <select name="year" class="form-select form-select-sm" style="min-width: 100px;">
                        @for($y=date('Y'); $y>=2023; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm px-3">Xem</button>
                </form>
            </div>

            {{-- 2. TỔNG QUAN (CARDS) --}}
            <div class="row g-3 mb-4">
                {{-- Tổng Lương --}}
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm bg-success text-white h-100">
                        <div class="card-body">
                            <div class="small text-uppercase opacity-75 fw-bold">Lương tổng (Ước tính)</div>
                            <div class="display-6 fw-bold mt-2">
                                {{ number_format($totalSalary, 0, ',', '.') }} <span class="fs-5">VND</span>
                            </div>
                            <div class="mt-2 small opacity-75 border-top pt-2" style="border-color: rgba(255,255,255,0.3) !important;">
                                <div>Cơ bản: {{ number_format($totalBaseSalary ?? 0, 0, ',', '.') }} đ</div>
                                <div>Đêm (30%): {{ number_format($totalNightAllowance ?? 0, 0, ',', '.') }} đ</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tổng Giờ --}}
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-white h-100">
                        <div class="card-body">
                            <div class="small text-uppercase text-muted fw-bold">Tổng giờ làm</div>
                            <div class="display-6 fw-bold mt-2 text-primary">
                                {{ number_format($totalHours, 1) }}h
                            </div>
                            <div class="small text-muted mt-1">
                                Trong đó đêm: <b>{{ number_format($totalNightHours, 1) }}h</b>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Chu kỳ --}}
                <div class="col-md-4">
                     <div class="card border-0 shadow-sm bg-light h-100">
                        <div class="card-body d-flex flex-column justify-content-center align-items-start">
                            <div class="text-muted small text-uppercase fw-bold mb-1">Chu kỳ lương</div>
                            <div>
                                Từ: <b>{{ $startDate->format('d/m/Y') }}</b> <br>
                                Đến: <b>{{ $endDate->format('d/m/Y') }}</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. BẢNG CHI TIẾT --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-list-check"></i> Chi tiết ca làm việc</h5>
                </div>
                {{-- Bỏ div class table-responsive để tránh bị cuộn ngang trên điện thoại --}}
                <div class="card-body p-0 p-md-3">
                    <table class="table table-hover align-middle mb-0 table-mobile-responsive">
                        <thead class="bg-light">
                            <tr>
                                <th>Ngày</th>
                                <th>Thời gian</th>
                                <th>Vị trí</th>
                                <th>Đơn giá</th>
                                <th class="text-center">Số giờ</th>
                                <th class="text-center">Đêm</th>
                                <th class="text-end">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payrollItems as $item)
                                <tr>
                                    {{-- Bổ sung data-label cho tất cả các thẻ td --}}
                                    <td data-label="Ngày">
                                        <div class="fw-bold">{{ $item['date'] }}</div>
                                        <small class="text-muted">{{ $item['day_name'] }}</small>
                                    </td>
                                    <td data-label="Thời gian">{{ $item['time'] }}</td>
                                    <td data-label="Vị trí">{{ $item['position'] }}</td>
                                    <td data-label="Đơn giá">
                                        @if(isset($item['rate']))
                                            <span class="badge bg-light text-dark border">{{ number_format($item['rate']) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td data-label="Số giờ" class="text-md-center fw-bold">{{ $item['hours'] }}</td>
                                    <td data-label="Giờ đêm" class="text-md-center {{ $item['night_hours'] > 0 ? 'text-primary fw-bold' : 'text-muted' }}">
                                        {{ $item['night_hours'] }}
                                    </td>
                                    <td data-label="Trạng thái" class="text-md-end">
                                        @if($item['status'] == 'Approved')
                                            <span class="badge bg-success">Đã duyệt</span>
                                        @elseif($item['status'] == 'StaffApproved')
                                            <span class="badge bg-info text-dark">Chờ QL duyệt</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        Không có ca làm việc nào trong khoảng thời gian này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- 4. FOOTER: GHI CHÚ MIỄN TRỪ TRÁCH NHIỆM --}}
                <div class="card-footer bg-white p-3">
                    <div class="alert alert-warning d-flex align-items-center mb-0 border-0 bg-warning bg-opacity-10 small">
                        <i class="bi bi-info-circle-fill me-3 fs-4 text-warning"></i>
                        <div>
                            <strong>Lưu ý quan trọng:</strong> <br>
                            - Bảng lương này được tính theo mức lương cơ bản từng vị trí (FOH: 26k, BOH: 28k) và phụ cấp đêm 30%. <br>
                            - Số tiền trên <b>chưa phải lương thực nhận chính thức</b> (chưa tính phụ cấp Delivery, thưởng, và chưa trừ BHXH, đồng phục, thuế...). <br>
                            - Bảng lương chính thức sẽ do bộ phận <b>Kế toán</b> quyết định.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection