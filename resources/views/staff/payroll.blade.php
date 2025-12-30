@extends('layouts.app')

@section('title', 'Bảng lương cá nhân')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            {{-- 1. HEADER & FILTER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold text-primary text-uppercase">
                    <i class="bi bi-wallet2 me-2"></i> Bảng lương của tôi
                </h4>
                
                <form action="{{ route('staff.payroll') }}" method="GET" class="d-flex gap-2">
                    <select name="month" class="form-select form-select-sm" style="width: 100px;">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng {{ $m }}</option>
                        @endfor
                    </select>
                    <select name="year" class="form-select form-select-sm" style="width: 100px;">
                        @for($y=date('Y'); $y>=2023; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Xem</button>
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
                        <div class="card-body d-flex flex-column justify-content-center">
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
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
                                    <td>
                                        <div class="fw-bold">{{ $item['date'] }}</div>
                                        <small class="text-muted">{{ $item['day_name'] }}</small>
                                    </td>
                                    <td>{{ $item['time'] }}</td>
                                    <td>{{ $item['position'] }}</td>
                                    <td>
                                        @if(isset($item['rate']))
                                            <span class="badge bg-light text-dark border">{{ number_format($item['rate']) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">{{ $item['hours'] }}</td>
                                    <td class="text-center {{ $item['night_hours'] > 0 ? 'text-primary fw-bold' : 'text-muted' }}">
                                        {{ $item['night_hours'] }}
                                    </td>
                                    <td class="text-end">
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