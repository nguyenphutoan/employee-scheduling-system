@extends('layouts.app')

@section('title', 'Hồ sơ của tôi')

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
                <div class="card-header bg-success text-white d-flex align-items-center">
                    <i class="bi bi-person-badge fs-4 me-2"></i>
                    <h5 class="mb-0 fw-bold">Thông tin cá nhân</h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('staff.profile.update') }}" method="POST">
                        @csrf
                        
                        {{-- 1. THÔNG TIN CHỈ XEM (READONLY) --}}
                        <div class="alert alert-light border mb-4">
                            <h6 class="text-muted fw-bold text-uppercase small mb-3">
                                <i class="bi bi-info-circle"></i> Thông tin cơ bản (Liên hệ quản lý nếu cần thay đổi)
                            </h6>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tên đăng nhập</label>
                                    <input type="text" class="form-control bg-light" value="{{ $user->UserName }}" readonly disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Họ và tên</label>
                                    <input type="text" class="form-control bg-light" value="{{ $user->FullName }}" readonly disabled>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="text" class="form-control bg-light" value="{{ $user->email }}" readonly disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Ngày vào làm</label>
                                    <input type="text" class="form-control bg-light" value="{{ date('d/m/Y', strtotime($user->StartDate)) }}" readonly disabled>
                                </div>
                            </div>
                        </div>

                        <hr>

                        {{-- 2. ĐỔI MẬT KHẨU (EDITABLE) --}}
                        <h6 class="text-primary fw-bold text-uppercase small mb-3">
                            <i class="bi bi-shield-lock"></i> Đổi mật khẩu
                        </h6>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu mới</label>
                            <input type="password" name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Nhập mật khẩu mới..." required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                            <input type="password" name="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Nhập lại mật khẩu mới..." required>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success px-4 fw-bold">
                                <i class="bi bi-save me-1"></i> Lưu mật khẩu mới
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection