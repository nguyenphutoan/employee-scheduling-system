@extends('layouts.app')

@section('title', 'Bảng lương ước tính')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            {{-- 1. BỘ LỌC THÁNG --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <form action="{{ route('staff.payroll') }}" method="GET" class="row align-items-center g-3">
                        <div class="col-auto">
                            <label class="fw-bold text-muted"><i class="bi bi-calendar-month"></i> Xem lương tháng:</label>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select form-select-sm">
                                @for($m=1; $m<=12; $m++)
                                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>Tháng {{ $m }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="year" class="form-select form-select-sm">
                                @for($y=date('Y'); $y>=2023; $y--)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>Năm {{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm px-3">Xem</button>
                        </div>
                        <div class="col text-end text-muted small fst-italic">
                            Chu kỳ: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                        </div>
                    </form>
                </div>
            </div>

            {{-- 2. TỔNG HỢP LƯƠNG --}}
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card shadow border-0 bg-primary text-white h-100">
                        <div class="card-body">
                            <h5 class="card-title text-uppercase opacity-75 small">Lương ước tính (Thực nhận)</h5>
                            <div class="display-4 fw-bold my-2">
                                {{ number_format($totalSalary, 0, ',', '.') }} <span class="fs-4">VND</span>
                            </div>
                            <div class="d-flex gap-3 mt-3">
                                <div class="bg-white bg-opacity-25 p-2 rounded px-3">
                                    <div class="small opacity-75">Giờ công (Approved)</div>
                                    <div class="fw-bold fs-5">{{ $totalHours }}h</div>
                                </div>
                                <div class="bg-white bg-opacity-25 p-2 rounded px-3">
                                    <div class="small opacity-75">Giờ đêm (>22h)</div>
                                    <div class="fw-bold fs-5">{{ $totalNightHours }}h</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-light fw-bold text-muted small text-uppercase">Công thức tính</div>
                        <div class="card-body small">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2 d-flex justify-content-between">
                                    <span>Lương cơ bản (25k/h):</span>
                                    <span class="fw-bold">{{ number_format($totalHours * 25000) }} đ</span>
                                </li>
                                <li class="mb-2 d-flex justify-content-between text-success">
                                    <span>Phụ cấp đêm (30%):</span>
                                    <span class="fw-bold">+ {{ number_format($totalNightHours * 25000 * 0.3) }} đ</span>
                                </li>
                                <li class="border-top pt-2 mt-2 d-flex justify-content-between fw-bold fs-6">
                                    <span>Tổng cộng:</span>
                                    <span class="text-primary">{{ number_format($totalSalary) }} đ</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. BẢNG CHI TIẾT --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-table"></i> Chi tiết phân công</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Ngày</th>
                                <th>Ca làm</th>
                                <th>Vị trí</th>
                                <th class="text-center">Số giờ</th>
                                <th class="text-center">Đêm</th>
                                <th class="text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payrollItems as $item)
                                <tr class="{{ $item['status'] == 'Approved' ? '' : 'opacity-50 bg-light' }}">
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $item['day_name'] }}</div>
                                        <small class="text-muted">{{ $item['date'] }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item['time'] }}</span>
                                    </td>
                                    <td>{{ $item['position'] }}</td>
                                    <td class="text-center fw-bold">{{ $item['hours'] }}</td>
                                    <td class="text-center {{ $item['night_hours'] > 0 ? 'text-primary fw-bold' : 'text-muted' }}">
                                        {{ $item['night_hours'] }}
                                    </td>
                                    <td class="text-center">
                                        @if($item['status'] == 'Approved')
                                            <span class="badge bg-danger">Approved</span>
                                        @elseif($item['status'] == 'StaffApproved')
                                            <span class="badge bg-success">Staff OK</span>
                                        @elseif($item['status'] == 'Submitted')
                                            <span class="badge bg-primary">Submitted</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Không có phân công nào trong chu kỳ này.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white p-3">
                    <div class="alert alert-warning d-flex align-items-center mb-0 border-0 bg-warning bg-opacity-10">
                        <i class="bi bi-info-circle-fill text-warning fs-4 me-3"></i>
                        <div>
                            <strong>Lưu ý:</strong> Đây là bảng lương <b>ước tính chưa chính thức</b>.
                            <br>
                            - Chỉ những ca có trạng thái <span class="badge bg-danger">Approved</span> mới được tính vào tổng lương.
                            <br>
                            - Bảng lương chính thức (bao gồm thưởng, phạt, phụ cấp khác...) sẽ do Quản lý gửi trực tiếp.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection