@extends('layouts.app')

@section('title', 'Quản lý nhân viên')

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ⛔ <strong>Lỗi nhập liệu:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="mb-0 fw-bold text-primary"><i class="bi bi-people-fill"></i> Danh sách nhân viên</h4>
        {{-- Nút Thêm nhân viên --}}
        @if(!Auth::user()->EndDate)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="bi bi-person-plus-fill"></i> Thêm nhân viên
            </button>
        @endif
    </div>

    {{-- BẢNG DANH SÁCH --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Tên đăng nhập</th> {{-- MỚI --}}
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
                        <td class="ps-3 text-muted">#{{ $user->UserID }}</td>
                        
                        {{-- Hiển thị UserName --}}
                        <td class="fw-bold text-primary">{{ $user->UserName }}</td>
                        
                        <td>{{ $user->FullName }}</td>
                        <td>{{ $user->email }}</td>

                        @php 
                            $Role = $user->Role ?? ''; 
                        @endphp
                        
                        <td>
                            @if($Role == 'Manager')
                                <span class="badge bg-danger">Quản lý</span>
                            @else
                                <span class="badge bg-info text-dark">Nhân viên</span>
                            @endif
                        </td>

                        <td>{{ $user->StartDate ? date('d/m/Y', strtotime($user->StartDate)) : '-' }}</td>

                        <td>
                            @if($user->EndDate)
                                <span class="badge bg-secondary">Đã nghỉ</span>
                            @else
                                <span class="badge bg-success">Đang làm</span>
                            @endif
                        </td>

                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end gap-2">
                                
                                {{-- KIỂM TRA: Nếu Manager chưa nghỉ việc (EndDate là null) thì được phép thao tác --}}
                                @if(!Auth::user()->EndDate && $user->Role != 'Manager')
                                
                                    {{-- 1. Nút Sửa --}}
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="editUser(
                                                {{ $user->UserID }}, 
                                                '{{ $user->UserName }}', 
                                                '{{ $user->FullName }}', 
                                                '{{ $user->email }}', 
                                                '{{ $Role }}', 
                                                '{{ $user->StartDate }}', 
                                                '{{ $user->EndDate }}'
                                            )">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    {{-- 2. Nút Xóa --}}
                                    <form action="{{ route('manager.employees.delete', $user->UserID) }}" method="POST" 
                                        onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                @elseif($user->Role == 'Manager')
                                    <span class="badge bg-warning text-dark border d-flex align-items-center" title="Đây là quản lý">
                                        <i class="bi bi-shield-lock-fill me-1"></i> Quản lý
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border d-flex align-items-center">
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

{{-- MODAL THÊM MỚI --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm nhân viên mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('manager.employees.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{-- Input UserName --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập (UserName) <span class="text-danger">*</span></label>
                        <input type="text" name="UserName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="FullName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quyền hạn</label>
                            <select name="Role" class="form-select">
                                <option value="Staff" selected>Nhân viên</option>
                                <option value="Manager">Quản lý</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ngày vào làm</label>
                            <input type="date" name="StartDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu nhân viên</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL CHỈNH SỬA --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Cập nhật thông tin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- Input UserName cho Edit --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên đăng nhập (UserName)</label>
                        <input type="text" name="UserName" id="editUserName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text" name="FullName" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới (Để trống nếu không đổi)</label>
                        <input type="password" name="password" class="form-control" placeholder="******">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Quyền hạn</label>
                            <select name="Role" id="editRole" class="form-select">
                                <option value="Staff">Nhân viên</option>
                                <option value="Manager">Quản lý</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ngày vào</label>
                            <input type="date" name="StartDate" id="editStartDate" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ngày nghỉ</label>
                            <input type="date" name="EndDate" id="editEndDate" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editUser(id, username, name, email, role, startDate, endDate) {
        // 1. Điền thông tin vào form
        document.getElementById('editUserName').value = username; 
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        
        document.getElementById('editStartDate').value = startDate ? startDate.split(' ')[0] : '';
        document.getElementById('editEndDate').value = endDate ? endDate.split(' ')[0] : '';

        // 2. Cập nhật action form
        var form = document.getElementById('editForm');
        form.action = "/manager/employees/" + id;

        // 3. Mở Modal
        var myModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
        myModal.show();
    }
</script>
@endsection