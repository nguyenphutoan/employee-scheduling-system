<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white fw-bold text-uppercase border-bottom-0">
        {{ $title }}
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-primary text-center">
                    <tr>
                        @foreach($positions as $pos)
                            <th style="min-width: 200px;">{{ $pos->PositionName }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($positions as $pos)
                            <td class="align-top p-2" style="height: 150px; background-color: #fff;">
                                @if($shift)
                                    @php
                                        $assignments = $shift->assignments->where('PositionID', $pos->PositionID);
                                    @endphp

                                    @foreach($assignments as $assign)
                                        <div class="card mb-2 shadow-sm border-start border-4 border-primary position-relative group-hover-action">
                                             <form action="{{ route('manager.delete_assignment', $assign->WaID) }}" 
                                                method="POST" 
                                                onsubmit="return confirm('Xóa phân công này?');">
                                                @csrf
                                                @method('DELETE')
                                                {{-- Chỉ giữ lại 1 thẻ i class bi-x-circle-fill --}}
                                                <button type="submit" class="btn btn-link text-danger p-0 ms-2 border-0" title="Xóa">
                                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                                </button>
                                            </form>
                                            <div class="card-body p-2 bg-light">
                                                <div class="fw-bold text-primary pe-3">{{ $assign->user->FullName ?? 'N/A' }}</div>
                                                <div class="d-flex justify-content-between mt-1 text-muted" style="font-size: 0.85rem;">
                                                    <span>{{ substr($assign->StartTime, 0, 5) }}</span>
                                                    <span>-</span>
                                                    <span>{{ substr($assign->EndTime, 0, 5) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <button type="button" 
                                            onclick="openAssignModal({{ $shift->ShiftID }}, {{ $pos->PositionID }}, '{{ $pos->PositionName }}')" 
                                            class="btn btn-outline-secondary w-100 border-dashed mt-2 py-2">
                                        <h5 class="mb-0 fw-bold">(+)</h5>
                                    </button>
                                @else
                                    <span class="text-muted text-center d-block mt-4">Chưa tạo Ca</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>