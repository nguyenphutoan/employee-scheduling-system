@extends('layouts.app')

@section('title', 'Quản lý nhân viên')

@section('content')
<div class="container-fluid">

    {{-- CSS RESPONSIVE CHO BẢNG NHÂN VIÊN --}}
    <style>
        .btn-responsive { width: 100%; }
        @media (min-width: 768px) {
            .btn-responsive { width: auto; }
        }

        @media (max-width: 767px) {
            .table-mobile-responsive thead { display: none; }
            .table-mobile-responsive tbody, 
            .table-mobile-responsive tr, 
            .table-mobile-responsive td { display: block; width: 100%; }
            
            .table-mobile-responsive tr { 
                margin-bottom: 1.5rem; 
                border: 1px solid #dee2e6; 
                border-radius: 0.75rem; 
                background-color: #fff; 
                box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
                overflow: hidden;
            }
            .table-mobile-responsive td { 
                display: flex; 
                align-items: center; 
                justify-content: space-between; 
                border: none; 
                padding: 0.75rem 1rem; 
                border-bottom: 1px dashed #e9ecef;
            }
            .table-mobile-responsive td:last-child { border-bottom: none; flex-direction: column; align-items: stretch; gap: 0.5rem; }
            
            /* Nhãn dữ liệu trên Mobile */
            .table-mobile-responsive td::before { 
                content: attr(data-label); 
                font-weight: bold; 
                color: #6c757d; 
                width: 40%;
                flex-shrink: 0;
            }

            /* Biến ô Họ Tên thành Header của Card */
            .table-mobile-responsive td:nth-child(3) { 
                background-color: #f8f9fa; 
                border-bottom: 2px solid #dee2e6; 
                justify-content: center;
                padding: 1rem;
            }
            .table-mobile-responsive td:nth-child(3)::before { display: none; }
            .table-mobile-responsive td:nth-child(3) { font-size: 1.25rem; font-weight: bold; color: #0d6efd; text-align: center; }
            
            /* Ô chức năng (Hành động) full width trên mobile */
            .table-mobile-responsive td .d-flex { width: 100%; justify-content: center !important; }
            .table-mobile-responsive td .d-flex button, .table-mobile-responsive td .d-flex form { flex: 1; }
            .table-mobile-responsive td .d-flex button { width: 100%; }
        }
    </style>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-start border-danger border-4" role="alert">
            ⛔ <strong>Lỗi nhập liệu:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HEADER (Đã tích hợp Flexbox Mobile) --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm gap-3">
        <h4 class="mb-0 fw-bold text-primary text-center text-md-start">
            <i class="bi bi-people-fill me-2"></i> Danh sách nhân viên
        </h4>
        @if(!Auth::user()->EndDate)
            <button class="btn btn-primary btn-responsive shadow-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bi bi-person-plus-fill me-1"></i> Thêm nhân viên
            </button>
        @endif
    </div>

    {{-- BẢNG DANH SÁCH --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0 p-md-3">
            <div class="table-responsive border-0">
                <table class="table table-hover align-middle mb-0 table-mobile-responsive">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Chức vụ</th>
                            <th>Ngày vào làm</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td data-label="Mã ID" class="ps-md-3 text-muted">#{{ $user->UserID }}</td>
                            <td data-label="Tài khoản" class="fw-bold text-primary">{{ $user->UserName }}</td>
                            <td data-label="Họ tên">{{ $user->FullName }}</td>
                            <td data-label="Email">{{ $user->email }}</td>

                            @php $Role = $user->Role ?? ''; @endphp
                            <td data-label="Chức vụ">
                                @if($Role == 'Manager')
                                    <span class="badge bg-danger">Quản lý</span>
                                @else
                                    <span class="badge bg-info text-dark">Nhân viên</span>
                                @endif
                            </td>

                            <td data-label="Ngày vào">{{ $user->StartDate ? date('d/m/Y', strtotime($user->StartDate)) : '-' }}</td>

                            <td data-label="Trạng thái">
                                @if($user->EndDate)
                                    <span class="badge bg-secondary">Đã nghỉ</span>
                                @else
                                    <span class="badge bg-success">Đang làm</span>
                                @endif
                            </td>

                            <td data-label="Hành động" class="text-md-end pe-md-3">
                                <div class="d-flex justify-content-end gap-2">
                                    @if(!Auth::user()->EndDate && $user->Role != 'Manager')
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="editUser(
                                                    {{ $user->UserID }}, '{{ $user->UserName }}', '{{ $user->FullName }}', 
                                                    '{{ $user->email }}', '{{ $Role }}', '{{ $user->StartDate }}', '{{ $user->EndDate }}'
                                                )" title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i> <span class="d-md-none ms-1">Sửa</span>
                                        </button>

                                        <form action="{{ route('manager.employees.delete', $user->UserID) }}" method="POST" 
                                            onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này?');" class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Xóa">
                                                <i class="bi bi-trash"></i> <span class="d-md-none ms-1">Xóa</span>
                                            </button>
                                        </form>
                                    @elseif($user->Role == 'Manager')
                                        <span class="badge bg-warning text-dark border d-flex align-items-center justify-content-center p-2 w-100 w-md-auto">
                                            <i class="bi bi-shield-lock-fill me-1"></i> Quản lý
                                        </span>
                                    @else
                                        <span class="badge bg-light text-secondary border d-flex align-items-center justify-content-center p-2 w-100 w-md-auto">
                                            <i class="bi bi-lock-fill me-1"></i> Chỉ xem
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL THÊM MỚI --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Thêm nhân viên mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('manager.employees.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" name="UserName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                        <input type="text" name="FullName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quyền hạn</label>
                            <select name="Role" class="form-select">
                                <option value="Staff" selected>Nhân viên</option>
                                <option value="Manager">Quản lý</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày vào làm</label>
                            <input type="date" name="StartDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Lưu nhân viên</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CHỈNH SỬA --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Cập nhật thông tin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập</label>
                        <input type="text" name="UserName" id="editUserName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ tên</label>
                        <input type="text" name="FullName" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mật khẩu mới <small class="text-muted fw-normal">(Để trống nếu không đổi)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Quyền hạn</label>
                            <select name="Role" id="editRole" class="form-select">
                                <option value="Staff">Nhân viên</option>
                                <option value="Manager">Quản lý</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ngày vào</label>
                            <input type="date" name="StartDate" id="editStartDate" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Ngày nghỉ</label>
                            <input type="date" name="EndDate" id="editEndDate" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editUser(id, username, name, email, role, startDate, endDate) {
        document.getElementById('editUserName').value = username; 
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        document.getElementById('editStartDate').value = startDate ? startDate.split(' ')[0] : '';
        document.getElementById('editEndDate').value = endDate ? endDate.split(' ')[0] : '';
        document.getElementById('editForm').action = "/manager/employees/" + id;
        var myModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
        myModal.show();
    }
</script>
@endsection