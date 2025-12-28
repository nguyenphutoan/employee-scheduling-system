@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    ✅ {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white d-flex align-items-center">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <h5 class="mb-0 fw-bold">Thông tin hồ sơ</h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('manager.profile.update') }}" method="POST">
                        @csrf
                        
                        {{-- 1. Tên đăng nhập & Họ tên --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tên đăng nhập (UserName)</label>
                                <input type="text" name="UserName" class="form-control" value="{{ $user->UserName }}" required>
                                <small class="text-muted">Dùng để đăng nhập hệ thống</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Họ và tên (FullName)</label>
                                <input type="text" name="FullName" class="form-control" value="{{ $user->FullName }}" required>
                            </div>
                        </div>

                        {{-- 2. Mật khẩu --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Đổi mật khẩu</label>
                            <input type="password" name="password" class="form-control" placeholder="Để trống nếu không muốn thay đổi">
                        </div>

                        <hr class="my-4 text-muted">

                        {{-- 3. Ngày làm việc --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày bắt đầu (StartDate)</label>
                                <input type="date" name="StartDate" class="form-control" value="{{ $user->StartDate }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-danger">Ngày nghỉ việc (EndDate)</label>
                                <input type="date" name="EndDate" class="form-control" value="{{ $user->EndDate }}">
                                <small class="text-muted">Chỉ điền khi bạn chính thức nghỉ việc</small>
                            </div>
                        </div>

                        {{-- 4. Quyền hạn (Role) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Vai trò (Role)</label>
                            <select name="Role" class="form-select bg-light">
                                <option value="Manager" {{ $user->Role == 'Manager' ? 'selected' : '' }}>Manager (Quản lý)</option>
                                <option value="Staff" {{ $user->Role == 'Staff' ? 'selected' : '' }}>Staff (Nhân viên)</option>
                            </select>
                            <div class="alert alert-warning mt-2 d-flex align-items-center mb-0 p-2">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <small>Lưu ý: Nếu bạn tự đổi thành <b>Staff</b>, bạn sẽ mất quyền truy cập vào trang quản lý ngay lập tức!</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            @if(!Auth::user()->EndDate)
                                <button type="reset" class="btn btn-secondary">Khôi phục</button>
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="bi bi-save me-1"></i> Cập nhật hồ sơ
                                </button>
                            @else
                                <div class="alert alert-warning mb-0 py-1 px-3 d-flex align-items-center">
                                    <i class="bi bi-lock-fill me-2"></i> Tài khoản đã ngưng hoạt động. Không thể chỉnh sửa.
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3 text-muted small">
                User ID: #{{ $user->UserID }}
            </div>
        </div>
    </div>
</div>
@endsection