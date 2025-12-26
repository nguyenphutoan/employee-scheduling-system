<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Week;
use App\Models\Shift;
use App\Models\Position;
use App\Models\EmpAvailability;
use Carbon\Carbon;
use App\Models\WorkAssignment;

class ManagerController extends Controller
{
   public function scheduling(Request $request)
    {
        // 1. Xác định ngày đang chọn (Mặc định là hôm nay nếu không chọn)
        $selectedDate = $request->input('date', Carbon::now()->format('Y-m-d'));
        // Tính toán ngày để nút bấm hoạt động (nhảy về đầu tuần của tuần trước/sau)
        $currentDateObj = Carbon::parse($selectedDate);
        // Nút "Tuần trước": Lùi 1 tuần và lấy ngày thứ 2
        $prevWeekDate = $currentDateObj->copy()->subWeek()->startOfWeek()->format('Y-m-d');
        // Nút "Tuần sau": Tăng 1 tuần và lấy ngày thứ 2
        $nextWeekDate = $currentDateObj->copy()->addWeek()->startOfWeek()->format('Y-m-d');

        $dayOfWeek = Carbon::parse($selectedDate)->format('D');

        // 2. Lấy thông tin Tuần chứa ngày đó
        $currentWeek = Week::where('StartDate', '<=', $selectedDate)
                            ->where('EndDate', '>=', $selectedDate)
                            ->first();

        // 3. Lấy danh sách Vị trí 
        $positions = Position::all();

        // 4. Lấy Ca Sáng & Ca Tối của ngày đó
        $morningShift = null;
        $eveningShift = null;
        $availableStaffs = collect(); // Danh sách nhân viên rảnh


        if ($currentWeek) {
            $morningShift = Shift::where('WeekID', $currentWeek->WeekID)
                                 ->where('DayOfWeek', $dayOfWeek)
                                 ->where('ShiftType', 'MOR')
                                 ->with('assignments.user', 'assignments.position')
                                 ->first();

            $eveningShift = Shift::where('WeekID', $currentWeek->WeekID)
                                 ->where('DayOfWeek', $dayOfWeek)
                                 ->where('ShiftType', 'EVE')
                                 ->with('assignments.user', 'assignments.position')
                                 ->first();
            
            // 5. Lấy danh sách nhân viên CÓ ĐĂNG KÝ RẢNH trong ngày này
            $availableStaffs = EmpAvailability::where('WeekID', $currentWeek->WeekID)
                                              ->where('DayOfWeek', $dayOfWeek)
                                              ->with('user')
                                              ->get();
        }
        // Tạo danh sách các ngày trong tuần để hiển thị thanh menu trên cùng
        $weekDates = [];
        if ($currentWeek) {
            $start = Carbon::parse($currentWeek->StartDate);
            for ($i = 0; $i < 7; $i++) {
                $date = $start->copy()->addDays($i);
                $weekDates[] = [
                    'date' => $date->format('Y-m-d'),
                    'dayName' => $this->getDayName($date->format('D')), 
                    'isToday' => $date->isToday(),
                    'isActive' => $date->format('Y-m-d') == $selectedDate
                ];
            }
        }

        return view('manager.scheduling', compact(
            'weekDates', 
            'selectedDate', 
            'positions', 
            'morningShift', 
            'eveningShift', 
            'availableStaffs',
            'prevWeekDate', 
            'nextWeekDate',
            'currentWeek',
        ));
    }

    // Hàm phụ chuyển đổi tên thứ sang tiếng Việt
    private function getDayName($day) {
        $map = ['Mon' => 'Thứ 2', 'Tue' => 'Thứ 3', 'Wed' => 'Thứ 4', 'Thu' => 'Thứ 5', 'Fri' => 'Thứ 6', 'Sat' => 'Thứ 7', 'Sun' => 'CN'];
        return $map[$day] ?? $day;
    }

    public function assignStaff(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'shift_id' => 'required',
            'user_id' => 'required',
            'position_id' => 'required',
            'start_time' => 'required', // Giờ bắt đầu ca
            'end_time' => 'required|after:start_time'   // Giờ kết thúc phải sau giờ bắt đầu
        ]);

        // 2. Lấy thông tin Ca làm việc để biết Thứ mấy (Mon, Tue...) và Tuần nào
        $shift = Shift::findOrFail($request->shift_id);

        // 3. LOGIC KIỂM TRA RÀNG BUỘC THỜI GIAN
        // Tìm lịch rảnh của nhân viên đó trong tuần đó, thứ đó
        $availability = EmpAvailability::where('UserID', $request->user_id)
                                       ->where('WeekID', $shift->WeekID)
                                       ->where('DayOfWeek', $shift->DayOfWeek)
                                       ->first();

        if (!$availability) {
            return back()->withErrors(['msg' => 'Nhân viên này chưa đăng ký lịch rảnh vào ngày này!']);
        }

        // So sánh thời gian
        // Logic: Giờ xếp >= Giờ rảnh bắt đầu AND Giờ xếp <= Giờ rảnh kết thúc
        $reqStart = strtotime($request->start_time);
        $reqEnd   = strtotime($request->end_time);
        $availStart = strtotime($availability->AvailableFrom);
        $availEnd   = strtotime($availability->AvailableTo);

        if ($reqStart < $availStart || $reqEnd > $availEnd) {
            
            return back()->withErrors([
                'msg' => "Không hợp lệ! Nhân viên chỉ rảnh từ " . substr($availability->AvailableFrom, 0, 5) . " đến " . substr($availability->AvailableTo, 0, 5)
            ]);
        }

        // 2. Kiểm tra xem nv đã được xếp lịch trong ca này hay chưa
        $isAssigned = WorkAssignment::where('ShiftID', $request->shift_id)
                            ->where('UserID', $request->user_id)
                            ->exists();

        if ($isAssigned) {
            return back()->withErrors(['msg' => 'Nhân viên này đã được xếp lịch trong ca này rồi!']);
        }

        // 3. Tạo phân công mới
        WorkAssignment::create([
            'ShiftID' => $request->shift_id,
            'UserID' => $request->user_id,
            'PositionID' => $request->position_id,
            'StartTime' => $request->start_time,
            'EndTime' => $request->end_time,
            'Status' => 'Draft'
        ]);

        return redirect()->route('manager.scheduling', ['date' => $request->date])
                        ->with('success', 'Đã xếp lịch thành công!');
    }

    public function createWeek(Request $request)
    {
        $date = $request->input('date'); 
        
        // 1. Tính toán ngày...
        $startOfWeek = Carbon::parse($date)->startOfWeek()->format('Y-m-d');
        $endOfWeek = Carbon::parse($date)->endOfWeek()->format('Y-m-d');

        // 2. Kiểm tra tồn tại...
        $exists = Week::where('StartDate', $startOfWeek)->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Tuần này đã tồn tại!');
        }

        // 3. Tạo Tuần mới
        $newWeek = Week::create([
            'StartDate' => $startOfWeek,
            'EndDate' => $endOfWeek
        ]);

        // 4. Tạo tự động các Ca làm việc
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        foreach ($days as $day) {
            Shift::create(['WeekID' => $newWeek->WeekID, 'DayOfWeek' => $day, 'ShiftType' => 'MOR']);
            Shift::create(['WeekID' => $newWeek->WeekID, 'DayOfWeek' => $day, 'ShiftType' => 'EVE']);
        }

        return redirect()->route('manager.scheduling', ['date' => $date])
                         ->with('success', 'Đã khởi tạo lịch làm việc cho tuần mới!');
    }

    public function deleteAssignment($id)
    {
        // 1. Tìm bản ghi phân công theo ID
        $assign = \App\Models\WorkAssignment::findOrFail($id);
        
        if ($assign->Status == 'Approved') {
            return back()->with('error', 'Không thể xóa lịch đã được duyệt công!');
        }

        $assign->delete();
        return back()->with('success', 'Đã xóa phân công!');
    }

    // Hàm chốt lịch (Chuyển Draft -> Submitted)
    public function submitWeek(Request $request)
    {
        $weekId = $request->input('week_id');

        // 1. Lấy tất cả các ShiftID thuộc tuần này
        $shiftIds = \App\Models\Shift::where('WeekID', $weekId)->pluck('ShiftID');

        // 2. Cập nhật trạng thái của TẤT CẢ phân công trong tuần này thành 'Submitted'
        \App\Models\WorkAssignment::whereIn('ShiftID', $shiftIds)
            ->where('Status', 'Draft') // Chỉ update cái nào đang nháp
            ->update(['Status' => 'Submitted']);

        return back()->with('success', 'Đã công khai lịch làm việc cho nhân viên!');
    }

    // Hàm hiển thị Bảng lịch tuần tổng hợp
    public function dashboard(Request $request)
    {
        // 1. Xác định tuần cần xem
        $weekId = $request->get('week_id');
        if ($weekId) {
            $currentWeek = \App\Models\Week::find($weekId);
        } else {
            // Mặc định lấy tuần hiện tại hoặc mới nhất
            $currentWeek = \App\Models\Week::where('StartDate', '<=', now())
                            ->where('EndDate', '>=', now())->first() 
                            ?? \App\Models\Week::latest('StartDate')->first();
        }

        // 1. Tìm tuần trước (Lấy tuần có ngày bắt đầu nhỏ hơn tuần hiện tại, sắp xếp giảm dần và lấy cái đầu tiên)
        $prevWeek = \App\Models\Week::where('StartDate', '<', $currentWeek->StartDate)
                                    ->orderBy('StartDate', 'desc')
                                    ->first();

        // 2. Tìm tuần sau (Lấy tuần có ngày bắt đầu lớn hơn tuần hiện tại, sắp xếp tăng dần và lấy cái đầu tiên)
        $nextWeek = \App\Models\Week::where('StartDate', '>', $currentWeek->StartDate)
                                    ->orderBy('StartDate', 'asc')
                                    ->first();

        // Nếu chưa có tuần nào
        if (!$currentWeek) {
            return view('manager.dashboard', ['noData' => true]);
        }

        // 2. Lấy danh sách 7 ngày trong tuần đó
        $start = \Carbon\Carbon::parse($currentWeek->StartDate);
        $weekDates = [];
        $daysMap = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        foreach ($daysMap as $i => $day) {
            $date = $start->copy()->addDays($i);
            $weekDates[$day] = [
                'date' => $date->format('d/m'),
                'full_date' => $date->format('Y-m-d'),
                'name' => $this->getDayName($day)
            ];
        }

        // 3. Lấy nhân viên (không phải Manager) và còn hiệu lực trong tuần hiện tại
        $users = \App\Models\User::where('Role', '!=', 'Manager') // Bỏ quản lý
            ->where(function($q) use ($currentWeek) {
                $q->whereNull('EndDate') // Trường hợp 1: Làm lâu dài (chưa có ngày nghỉ)
                ->orWhere('EndDate', '>=', $currentWeek->StartDate); // Trường hợp 2: Có ngày nghỉ, nhưng nghỉ SAU khi tuần này bắt đầu
            })
            //Phải vào làm TRƯỚC khi tuần này kết thúc
            ->where('StartDate', '<=', $currentWeek->EndDate) 
            ->orderBy('FullName', 'asc')
            ->get();

        // 4. Lấy tất cả phân công trong tuần này
        $assignments = \App\Models\WorkAssignment::whereHas('shift', function($q) use ($currentWeek) {
                            $q->where('WeekID', $currentWeek->WeekID);
                        })->with(['shift', 'position'])->get();

        $schedule = [];
        $totalHours = [];
        $schedule = [];
        $dailyTotals = array_fill_keys($daysMap, 0); 
        $grandTotal = 0;

        foreach ($users as $user) {
            $totalHours[$user->UserID] = 0;
            
            foreach ($daysMap as $day) {
                // Tìm các ca làm của user này trong ngày này
                $userAssigns = $assignments->filter(function($a) use ($user, $day) {
                    return $a->UserID == $user->UserID && $a->shift->DayOfWeek == $day;
                });

                $cellData = [];
                foreach ($userAssigns as $assign) {
                    // Format giờ: 08:00-14:00
                    $startT = substr($assign->StartTime, 0, 5);
                    $endT = substr($assign->EndTime, 0, 5);
                   // 1. Lấy tên vị trí (Nếu có)
                    $posName = $assign->position->PositionName ?? '';

                    // Cộng dồn giờ công
                    $t1 = \Carbon\Carbon::parse($assign->StartTime);
                    $t2 = \Carbon\Carbon::parse($assign->EndTime);
                    if ($t2->lt($t1)) $t2->addDay();
                    $hours = abs($t2->diffInMinutes($t1)) / 60;
                    $totalHours[$user->UserID] += $hours;
                    $dailyTotals[$day] += $hours;
                    $grandTotal += $hours;

                    // Xác định màu
                    $bgClass = 'bg-secondary'; // Draft (Xám)
                    if ($assign->Status == 'Submitted') $bgClass = 'bg-primary'; // Xanh dương
                    if ($assign->Status == 'StaffApproved') $bgClass = 'bg-success'; // Xanh lá
                    if ($assign->Status == 'Approved') $bgClass = 'bg-danger'; // Đỏ

                    // Nội dung HTML
                    $html = "<div class='p-1 mb-1 rounded text-white small $bgClass position-relative'>";
                    $html .= "<div>" . substr($assign->StartTime, 0, 5) . " - " . substr($assign->EndTime, 0, 5) . "</div>";
                    $html .= "<div class='opacity-75' style='font-size: 0.75rem'>(" . ($assign->position->PositionName ?? '') . ")</div>";

                    // Nút Duyệt (Chỉ hiện khi Status là StaffApproved)
                    if ($assign->Status == 'StaffApproved') {
                        $approveUrl = route('manager.approve_assignment', $assign->WaID);
                        $csrf = csrf_token();
                        $html .= "
                            <form action='$approveUrl' method='POST' class='mt-1'>
                                <input type='hidden' name='_token' value='$csrf'>
                                <button type='submit' class='btn btn-light btn-sm w-100 p-0 text-success fw-bold' style='font-size: 0.7rem'>
                                    <i class='bi bi-check'></i> Duyệt
                                </button>
                            </form>
                        ";
                    } elseif ($assign->Status == 'Approved') {
                         $html .= "<i class='bi bi-check-all position-absolute top-0 end-0 m-1'></i>";
                    }

                    $html .= "</div>";
                    $cellData[] = $html;
                }

                // Lưu vào mảng (nối bằng xuống dòng nếu làm 2 ca 1 ngày)
                $schedule[$user->UserID][$day] = implode('<hr class="my-1 border-light">', $cellData);
            }
        }

        return view('manager.dashboard', compact(
            'currentWeek', 'weekDates', 'users', 'schedule', 'totalHours', 'daysMap', 
            'prevWeek', 'nextWeek',
            'dailyTotals', 'grandTotal'
        ));
    }

    // 1. Hiển thị danh sách nhân viên
    public function indexEmployees()
    {
        // Lấy danh sách nhân viên 
        $users = \App\Models\User::all();
        
        // Lấy danh sách vị trí để hiện trong form thêm mới
        $positions = \App\Models\Position::all(); 

        return view('manager.employees', compact('users', 'positions'));
    }

    // 1. THÊM NHÂN VIÊN
    public function storeEmployee(Request $request)
    {
        $request->validate([
            'UserName' => 'required|string|max:50|unique:users,UserName',
            'FullName' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'Role'     => 'required',
            'StartDate'=> 'required|date',
        ]);

        \App\Models\User::create([
            'UserName' => $request->UserName,
            'FullName' => $request->FullName,
            'email'    => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'Role'     => $request->Role,
            'StartDate'=> $request->StartDate,
            'EndDate'  => null,
        ]);

        return back()->with('success', 'Đã thêm nhân viên thành công!');
    }

    // 2. CẬP NHẬT NHÂN VIÊN 
    public function updateEmployee(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        if ($user->Role == 'Manager') {
            return back()->with('error', 'Bạn không có quyền chỉnh sửa thông tin của Quản lý khác!');
        }

        $request->validate([
            // Check trùng UserName, nhưng trừ chính User đang sửa ra
            'UserName' => 'required|string|max:50|unique:users,UserName,'.$user->UserID.',UserID', 
            'FullName' => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,'.$user->UserID.',UserID',
            'Role'     => 'required',
            'StartDate'=> 'required|date',
            'EndDate'  => 'nullable|date|after_or_equal:StartDate',
        ]);

        $data = [
            'UserName' => $request->UserName, 
            'FullName' => $request->FullName,
            'email'    => $request->email,
            'Role'     => $request->Role,
            'StartDate'=> $request->StartDate,
            'EndDate'  => $request->EndDate,
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Đã cập nhật thông tin nhân viên!');
    }

    // 4. Xóa nhân viên
    public function deleteEmployee($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Kiểm tra: Không cho xóa chính mình
        if ($user->UserID == auth()->id()) {
            return back()->with('error', 'Bạn không thể tự xóa chính mình!');
        }

        if ($user->Role == 'Manager') {
            return back()->with('error', 'Bạn không thể xóa tài khoản Quản lý!');
        }

        $user->delete();
        return back()->with('success', 'Đã xóa nhân viên!');
    }

    public function showProfile()
    {
        // Lấy thông tin người đang đăng nhập
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('manager.profile', compact('user'));
    }

    // 2. Cập nhật hồ sơ
    public function updateProfile(Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user(); // Lấy user hiện tại (Model)
        
        // Validate dữ liệu
        $request->validate([
            'UserName' => 'required|string|max:50|unique:users,UserName,'.$user->UserID.',UserID',
            'FullName' => 'required|string|max:100',
            'StartDate'=> 'required|date',
            'EndDate'  => 'nullable|date|after_or_equal:StartDate',
            'password' => 'nullable|min:6', // Mật khẩu không bắt buộc (nếu không đổi)
            'Role'     => 'required|in:Staff,Manager'
        ]);

        // Chuẩn bị dữ liệu update
        $data = [
            'UserName' => $request->UserName,
            'FullName' => $request->FullName,
            'StartDate'=> $request->StartDate,
            'EndDate'  => $request->EndDate,
            'Role'     => $request->Role
        ];

        // Nếu có nhập mật khẩu mới thì mới mã hóa và lưu
        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->Password);
        }

        \App\Models\User::where('UserID', $user->UserID)->update($data);

        return back()->with('success', 'Cập nhật hồ sơ thành công!');
    }

    // 1. Hàm duyệt công
    public function approveAssignment($id)
    {
        $assign = \App\Models\WorkAssignment::findOrFail($id);
        
        // Chỉ duyệt được khi nhân viên đã confirm
        if ($assign->Status == 'StaffApproved') {
            $assign->Status = 'Approved';
            $assign->save();
            return back()->with('success', 'Đã duyệt công!');
        }
        return back()->with('error', 'Chưa thể duyệt (Nhân viên chưa xác nhận).');
    }

    // --- QUẢN LÝ BẢNG LƯƠNG (PAYROLL) ---
    public function payroll(Request $request)
    {
        // 1. Lấy tháng/năm từ request (Mặc định hiện tại)
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        // 2. Xác định chu kỳ lương (21 tháng trước -> 20 tháng này)
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 20)->endOfDay();
        $startDate = $endDate->copy()->subMonth()->addDay()->startOfDay();

        // 3. Lấy danh sách nhân viên 
        $users = \App\Models\User::all();

        $payrollData = [];
        $grandTotalHours = 0;
        $grandTotalSalary = 0;
        $dayMap = ['Mon' => 0, 'Tue' => 1, 'Wed' => 2, 'Thu' => 3, 'Fri' => 4, 'Sat' => 5, 'Sun' => 6];

        foreach ($users as $user) {
            // Lấy lịch APPROVED của user này trong khoảng thời gian
            // (Lấy rộng ra các tuần liên quan rồi lọc ngày sau)
            $assignments = \App\Models\WorkAssignment::where('UserID', $user->UserID)
                ->where('Status', 'Approved') // Chỉ tính lịch đã duyệt
                ->with(['shift.week', 'position'])
                ->get();

            $userTotalHours = 0;
            $userNightHours = 0;
            $details = [];

            foreach ($assignments as $assign) {
                $weekStart = \Carbon\Carbon::parse($assign->shift->week->StartDate);
                $dayOffset = $dayMap[$assign->shift->DayOfWeek] ?? 0;
                $workDate = $weekStart->copy()->addDays($dayOffset);

                // Chỉ xử lý nếu nằm trong chu kỳ lương
                if ($workDate->between($startDate, $endDate)) {
                    
                    // Tính giờ làm
                    $t1 = \Carbon\Carbon::parse($assign->StartTime);
                    $t2 = \Carbon\Carbon::parse($assign->EndTime);
                    if ($t2->lt($t1)) $t2->addDay();
                    
                    $hours = abs($t2->diffInMinutes($t1)) / 60;

                    // Tính giờ đêm (Logic giống bên Staff)
                    $nightHours = 0;
                    $nightStart = \Carbon\Carbon::parse($assign->StartTime)->setTime(22, 0);
                    $nightEnd = $nightStart->copy()->addDay()->setTime(6, 0);
                    $earlyMorningStart = \Carbon\Carbon::parse($assign->StartTime)->setTime(0, 0);
                    $earlyMorningEnd = \Carbon\Carbon::parse($assign->StartTime)->setTime(6, 0);

                    // Giao thoa 22h-6h sáng hôm sau
                    $overlapStart = max($t1->timestamp, $nightStart->timestamp);
                    $overlapEnd = min($t2->timestamp, $nightEnd->timestamp);
                    if ($overlapEnd > $overlapStart) $nightHours += ($overlapEnd - $overlapStart) / 3600;

                    // Giao thoa 0h-6h sáng hôm nay
                    $overlapStart2 = max($t1->timestamp, $earlyMorningStart->timestamp);
                    $overlapEnd2 = min($t2->timestamp, $earlyMorningEnd->timestamp);
                    if ($overlapEnd2 > $overlapStart2) $nightHours += ($overlapEnd2 - $overlapStart2) / 3600;

                    // Cộng dồn cá nhân
                    $userTotalHours += $hours;
                    $userNightHours += $nightHours;

                    // Lưu chi tiết để hiện trong Modal
                    $details[] = [
                        'date' => $workDate->format('d/m/Y'),
                        'time' => substr($assign->StartTime, 0, 5) . ' - ' . substr($assign->EndTime, 0, 5),
                        'position' => $assign->position->PositionName ?? '',
                        'hours' => $hours,
                        'night' => $nightHours
                    ];
                }
            }

            // Tính lương cá nhân
            $baseSalary = $userTotalHours * 25000;
            $nightAllowance = $userNightHours * 25000 * 0.3;
            $totalSalary = $baseSalary + $nightAllowance;

            // Cộng dồn tổng công ty
            $grandTotalHours += $userTotalHours;
            $grandTotalSalary += $totalSalary;

            // Đẩy vào mảng dữ liệu nếu có làm việc (hoặc hiển thị cả người làm 0h tùy bạn)
            if($userTotalHours > 0 || count($details) > 0) {
                $payrollData[] = (object) [
                    'user' => $user,
                    'total_hours' => $userTotalHours,
                    'night_hours' => $userNightHours,
                    'salary' => $totalSalary,
                    'details' => $details
                ];
            }
        }

        return view('manager.payroll', compact(
            'payrollData', 'grandTotalHours', 'grandTotalSalary', 
            'month', 'year', 'startDate', 'endDate'
        ));
    }
}