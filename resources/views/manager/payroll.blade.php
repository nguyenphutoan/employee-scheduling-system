@extends('layouts.app')

@section('title', 'Bảng lương toàn nhân viên')

@section('content')
<div class="container-fluid">
    
    {{-- 1. BỘ LỌC VÀ HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold text-primary text-uppercase">
            <i class="bi bi-cash-stack me-2"></i> Bảng lương tháng {{ $month }}/{{ $year }}
        </h4>
        
        <form action="{{ route('manager.payroll') }}" method="GET" class="d-flex gap-2">
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

    {{-- 2. THỐNG KÊ TỔNG QUAN (CARDS) --}}
    <div class="row mb-4">
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

        <div class="col-md-12 col-xl-4 mt-3 mt-xl-0">
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
                        * Chỉ tính các ca đã có trạng thái <b>Approved</b>.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. DANH SÁCH CHI TIẾT --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-people"></i> Chi tiết nhân viên</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        {{ substr($data->user->FullName, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $data->user->FullName }}</div>
                                        <div class="small text-muted">{{ $data->user->UserName }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-bold">{{ number_format($data->total_hours, 1) }}</td>
                            <td class="text-center text-muted">{{ number_format($data->night_hours, 1) }}</td>
                            <td class="text-end pe-4">
                                <span class="badge bg-success fs-6 fw-normal">
                                    {{ number_format($data->salary, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                {{-- Nút mở Modal --}}
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailModal-{{ $data->user->UserID }}">
                                    Xem <i class="bi bi-eye-fill ms-1"></i>
                                </button>

                                {{-- MODAL CHI TIẾT (Mỗi user 1 modal) --}}
                                <div class="modal fade" id="detailModal-{{ $data->user->UserID }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title fw-bold">Chi tiết công: {{ $data->user->FullName }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-0">
                                                <table class="table table-striped mb-0 text-start">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th class="ps-4">Ngày</th>
                                                            <th>Ca làm</th>
                                                            <th>Vị trí</th>
                                                            <th class="text-center">Giờ làm</th>
                                                            <th class="text-center">Đêm</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($data->details as $item)
                                                            <tr>
                                                                <td class="ps-4">{{ $item['date'] }}</td>
                                                                <td>{{ $item['time'] }}</td>
                                                                <td>{{ $item['position'] }}</td>
                                                                <td class="text-center fw-bold">{{ number_format($item['hours'], 1) }}</td>
                                                                <td class="text-center {{ $item['night'] > 0 ? 'text-primary fw-bold' : 'text-muted' }}">
                                                                    {{ number_format($item['night'], 1) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        @if(count($data->details) == 0)
                                                            <tr><td colspan="5" class="text-center">Không có dữ liệu approved</td></tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="modal-footer bg-light d-flex justify-content-between">
                                                <div class="small text-muted">
                                                    Tổng: <b>{{ number_format($data->total_hours, 1) }}h</b> 
                                                    (Đêm: {{ number_format($data->night_hours, 1) }}h)
                                                </div>
                                                <div class="fw-bold text-primary fs-5">
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
                                Không tìm thấy dữ liệu chấm công đã duyệt trong tháng này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-3">
             <div class="alert alert-warning mb-0 border-0 bg-warning bg-opacity-10 small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Lưu ý quan trọng:</strong> Bảng lương này được tính dựa trên số giờ công có trạng thái <b>Approved</b> và các công thức phụ cấp cơ bản (Đêm 30%). 
                Số liệu này chỉ mang tính chất tham khảo (Ước tính). Số lương thực nhận cuối cùng (Net Salary) sau khi cộng thưởng, trừ phạt, BHXH, thuế... phải do bộ phận <b>Kế toán</b> quyết định.
            </div>
        </div>
    </div>
</div>
@endsection