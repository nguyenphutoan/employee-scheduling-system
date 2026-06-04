<style>
    /* CSS RESPONSIVE ĐỘC QUYỀN CHO BẢNG CHIA CA */
    @media (max-width: 767px) {
        .shift-table-responsive thead { display: none; }
        .shift-table-responsive tbody, 
        .shift-table-responsive tr, 
        .shift-table-responsive td { display: block; width: 100%; }
        
        .shift-table-responsive td { 
            height: auto !important; 
            border: 1px solid #dee2e6 !important; 
            border-radius: 0.5rem; 
            margin-bottom: 1.5rem; 
            padding: 1rem !important; 
            background-color: #f8f9fa !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
    }
    
    /* Thiết lập cho PC */
    @media (min-width: 768px) {
        .shift-table-responsive th { min-width: 200px; }
        .shift-table-responsive td { height: 150px; background-color: #fff; }
    }
</style>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-header bg-info text-white fw-bold text-uppercase border-0 rounded-top shadow-sm">
        {{ $title }}
    </div>
    <div class="card-body p-0 p-md-2">
        <div class="table-responsive border-0 hide-scrollbar">
            <table class="table table-bordered mb-0 shift-table-responsive">
                <thead class="table-primary text-center">
                    <tr>
                        @foreach($positions as $pos)
                            <th>{{ $pos->PositionName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($positions as $pos)
                            <td class="align-top p-2 p-md-3">
                                
                                {{-- HEADER VỊ TRÍ CHỈ HIỂN THỊ TRÊN MOBILE --}}
                                <div class="d-md-none mb-3 fw-bold text-uppercase text-primary border-bottom border-primary border-2 pb-2 d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-person-workspace me-2"></i> {{ $pos->PositionName }}</span>
                                </div>

                                @if($shift)
                                    @php
                                        $assignments = $shift->assignments->where('PositionID', $pos->PositionID);
                                    @endphp

                                    @foreach($assignments as $assign)
                                        {{-- THẺ NHÂN VIÊN --}}
                                        <div class="card mb-2 shadow-sm border-0 border-start border-4 border-primary position-relative group-hover-action">
                                            
                                            {{-- NÚT XÓA: Đã dùng position-absolute để ghim lên góc phải --}}
                                            <form action="{{ route('manager.delete_assignment', $assign->WaID) }}" 
                                                method="POST" 
                                                class="position-absolute top-0 end-0 m-1"
                                                onsubmit="return confirm('Xóa phân công này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0 bg-transparent" title="Xóa">
                                                    <i class="bi bi-x-circle-fill fs-5 opacity-75 hover-opacity-100"></i>
                                                </button>
                                            </form>

                                            {{-- NỘI DUNG THẺ --}}
                                            <div class="card-body p-2 bg-white pe-4"> {{-- Thêm pe-4 để chữ không đè lên nút xóa --}}
                                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.95rem;">
                                                    {{ $assign->user->FullName ?? 'N/A' }}
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-2 text-muted" style="font-size: 0.8rem;">
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-clock"></i> {{ substr($assign->StartTime, 0, 5) }}
                                                    </span>
                                                    <span class="text-muted fw-bold">-</span>
                                                    <span class="badge bg-light text-dark border">
                                                        {{ substr($assign->EndTime, 0, 5) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- NÚT THÊM NHÂN VIÊN LÀM ĐẸP LẠI (Dashed border) --}}
                                    <button type="button" 
                                            onclick="openAssignModal({{ $shift->ShiftID }}, {{ $pos->PositionID }}, '{{ $pos->PositionName }}')" 
                                            class="btn btn-light w-100 mt-2 py-2 text-primary" 
                                            style="border: 2px dashed #0d6efd; background-color: rgba(13,110,253,0.05);">
                                        <i class="bi bi-plus-lg fw-bold"></i> <span class="fw-bold">Thêm NV</span>
                                    </button>
                                @else
                                    <span class="text-muted text-center d-block mt-4">
                                        <i class="bi bi-inbox fs-4 d-block mb-1 opacity-50"></i>
                                        Chưa tạo Ca
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>