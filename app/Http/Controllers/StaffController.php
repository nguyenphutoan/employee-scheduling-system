<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Week;
use App\Models\EmpAvailability;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkAssignment;

class StaffController extends Controller
{
    // 1. Hiển thị form đăng ký
    public function index(Request $request)
    {
        // Mặc định lấy tuần tiếp theo (Next Week) để đăng ký
        // Hoặc lấy ngày từ URL nếu nhân viên muốn xem lại tuần cũ
        $date = $request->input('date', Carbon::now()->addWeek()->startOfWeek()->format('Y-m-d'));
        
        $startOfWeek = Carbon::parse($date)->startOfWeek();
        $endOfWeek = Carbon::parse($date)->endOfWeek();

        // Tìm xem tuần này đã được Quản lý tạo chưa
        $week = Week::where('StartDate', $startOfWeek->format('Y-m-d'))->first();

        // Lấy dữ liệu cũ nhân viên đã đăng ký (nếu có) để hiển thị lại
        $myAvailabilities = [];
        if ($week) {
            $data = EmpAvailability::where('WeekID', $week->WeekID)
                                   ->where('UserID', Auth::id())
                                   ->get();
            // Chuyển về dạng mảng cho dễ dùng ở View: ['Mon' =>Object, 'Tue' => Object...]
            foreach($data as $item) {
                $myAvailabilities[$item->DayOfWeek] = $item;
            }
        }

        // Tạo danh sách 7 ngày để hiển thị ra bảng
        $weekDays = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        foreach ($days as $index => $dayCode) {
            $currentDay = $startOfWeek->copy()->addDays($index);
            $weekDays[] = [
                'code' => $dayCode, // Mon, Tue...
                'name' => $this->getDayName($dayCode), // Thứ 2, Thứ 3...
                'date' => $currentDay->format('d/m/Y'),
                'full_date' => $currentDay->format('Y-m-d')
            ];
        }

        return view('staff.register', compact('week', 'weekDays', 'myAvailabilities', 'date'));
    }

    // 2. Lưu dữ liệu đăng ký
    public function store(Request $request)
    {
        $request->validate([
            'week_id' => 'required|exists:weeks,WeekID',
        ]);

        $userId = Auth::id();
        $weekId = $request->week_id;
        $inputs = $request->input('availability'); // Mảng dữ liệu từ form

        // Duyệt qua từng ngày (Mon, Tue...)
        foreach ($inputs as $day => $times) {
            // TRƯỜNG HỢP 1: Người dùng có nhập đủ giờ
            if (!empty($times['start']) && !empty($times['end'])) {
                
                // --- LOGIC MỚI: Kiểm tra Giờ Bắt đầu < Giờ Kết thúc ---
                if ($times['start'] >= $times['end']) {
                    // Lấy tên ngày tiếng Việt để báo lỗi cho dễ hiểu
                    $dayName = $this->getDayName($day); 
                    return back()->withErrors(['msg' => "Lỗi tại $dayName: Giờ 'Rảnh từ' phải nhỏ hơn giờ 'Đến'!"])->withInput();
                }

                // Nếu hợp lệ thì Lưu/Cập nhật
                EmpAvailability::updateOrCreate(
                    [
                        'UserID' => $userId,
                        'WeekID' => $weekId,
                        'DayOfWeek' => $day
                    ],
                    [
                        'AvailableFrom' => $times['start'],
                        'AvailableTo' => $times['end']
                    ]
                );
            } 
            // TRƯỜNG HỢP 2: Người dùng để trống (hoặc đã bấm nút xóa trên giao diện)
            else {
                // Xóa bản ghi trong Database
                EmpAvailability::where('UserID', $userId)
                            ->where('WeekID', $weekId)
                            ->where('DayOfWeek', $day)
                            ->delete();
            }
        }

        return back()->with('success', 'Đã lưu lịch đăng ký thành công!');
    }

    private function getDayName($code) {
        $map = ['Mon'=>'Thứ 2', 'Tue'=>'Thứ 3', 'Wed'=>'Thứ 4', 'Thu'=>'Thứ 5', 'Fri'=>'Thứ 6', 'Sat'=>'Thứ 7', 'Sun'=>'Chủ Nhật'];
        return $map[$code] ?? $code;
    }

    public function dashboard(Request $request)
    {
        $userId = Auth::id();

        // 1. XÁC ĐỊNH TUẦN CẦN XEM
        // Ưu tiên lấy từ request, nếu không có thì tìm tuần hiện tại
        $weekId = $request->get('week_id');
        if ($weekId) {
            $currentWeek = \App\Models\Week::find($weekId);
        } else {
            // Tìm tuần chứa ngày hôm nay
            $currentWeek = \App\Models\Week::where('StartDate', '<=', now())
                            ->where('EndDate', '>=', now())->first();
            
            // Nếu không có (ví dụ chưa tạo lịch tuần này), lấy tuần mới nhất trong DB
            if (!$currentWeek) {
                $currentWeek = \App\Models\Week::latest('StartDate')->first();
            }
        }

        // Trường hợp Database chưa có bất kỳ tuần nào
        if (!$currentWeek) {
            return view('staff.dashboard', ['noData' => true]);
        }

        // 2. TÌM TUẦN TRƯỚC VÀ TUẦN SAU (Để làm nút điều hướng)
        $prevWeek = \App\Models\Week::where('StartDate', '<', $currentWeek->StartDate)
                        ->orderBy('StartDate', 'desc')->first();
        $nextWeek = \App\Models\Week::where('StartDate', '>', $currentWeek->StartDate)
                        ->orderBy('StartDate', 'asc')->first();

        // 3. TẠO DATA CHO HEADER BẢNG (Thứ 2 - CN)
        $start = \Carbon\Carbon::parse($currentWeek->StartDate);
        $daysMap = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weekDates = [];
        
        foreach ($daysMap as $i => $dayCode) {
            $date = $start->copy()->addDays($i); // Cộng thêm i ngày vào ngày bắt đầu
            $weekDates[$dayCode] = [
                'date' => $date->format('d/m'), // VD: 22/12
                'name_vn' => $this->getDayName($dayCode) // <--- GỌI HÀM CÓ SẴN CỦA BẠN
            ];
        }

        // 4. LẤY LỊCH LÀM VIỆC (Chỉ lấy trạng thái Submitted)
        $assignments = \App\Models\WorkAssignment::where('UserID', $userId)
                        ->where('Status', '!=', 'Draft') 
                        ->whereHas('shift', function($q) use ($currentWeek) {
                            $q->where('WeekID', $currentWeek->WeekID);
                        })
                        ->with(['shift', 'position']) // Load kèm bảng phụ để tránh query nhiều lần
                        ->get();

        // 5. XỬ LÝ DỮ LIỆU VÀO TỪNG Ô (Mapping)
        $mySchedule = array_fill_keys($daysMap, []); // Tạo khung rỗng cho 7 ngày
        $totalHours = 0;

        foreach ($assignments as $assign) {
            $dayCode = $assign->shift->DayOfWeek; // VD: 'Mon', 'Tue'...
            
            // Tính giờ làm (Xử lý cả ca qua đêm)
            $t1 = \Carbon\Carbon::parse($assign->StartTime);
            $t2 = \Carbon\Carbon::parse($assign->EndTime);
            
            if ($t2->lt($t1)) {
                $t2->addDay(); // Nếu giờ kết thúc nhỏ hơn giờ bắt đầu -> Qua hôm sau
            }
            
            $hours = abs($t2->diffInMinutes($t1)) / 60;
            $totalHours += $hours;

            // Đẩy dữ liệu vào ngày tương ứng
            $mySchedule[$dayCode][] = [
                'id' => $assign->WaID, // <--- THÊM ID
                'status' => $assign->Status, // <--- THÊM STATUS
                'start' => substr($assign->StartTime, 0, 5), // Lấy 08:00
                'end' => substr($assign->EndTime, 0, 5),     // Lấy 14:00
                'position' => $assign->position->PositionName ?? 'N/A',
                'hours' => number_format($hours, 1) // Làm tròn 1 số thập phân
            ];
        }

        // 6. TRẢ VỀ VIEW
        return view('staff.dashboard', compact(
            'currentWeek', 'prevWeek', 'nextWeek', 
            'weekDates', 'mySchedule', 'daysMap', 'totalHours'
        ));
    }

    // 1. Hiển thị trang hồ sơ
    public function showProfile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return view('staff.profile', compact('user'));
    }

    // 2. Cập nhật mật khẩu (Chỉ cho phép đổi pass)
    public function updateProfile(Request $request)
    {
        $request->validate([
            // password_confirmation là trường bắt buộc phải có trong form để xác nhận
            'password' => 'required|min:6|confirmed', 
        ], [
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();

        // Chỉ cập nhật duy nhất cột password
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    public function confirmAssignment($id)
    {
        $assignment = \App\Models\WorkAssignment::findOrFail($id);

        // Kiểm tra bảo mật: Chỉ xác nhận lịch của chính mình
        if ($assignment->UserID != auth()->id()) {
            return back()->with('error', 'Không có quyền!');
        }

        // Logic: Chỉ được xác nhận nếu trạng thái là Submitted
        if ($assignment->Status == 'Submitted') {
            $assignment->Status = 'StaffApproved';
            $assignment->save();
            return back()->with('success', 'Đã xác nhận công!');
        }

        return back()->with('error', 'Trạng thái không hợp lệ.');
    }

    // --- BẢNG LƯƠNG (PAYROLL) ---
    public function payroll(Request $request)
    {
        $userId = Auth::id();

        // 1. Lấy tháng/năm từ request (Mặc định là tháng hiện tại)
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        // 2. Xác định chu kỳ lương (21 tháng trước - 20 tháng này)
        // Ví dụ: Xem lương Tháng 2 => Từ 21/01 đến 20/02
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 20)->endOfDay();
        $startDate = $endDate->copy()->subMonth()->addDay()->startOfDay(); // 21 tháng trước

        // 3. Lấy tất cả phân công của user (Eager load Shift và Week để tính ngày)
        // Lưu ý: Lấy rộng ra các tuần có khả năng dính vào khoảng thời gian này để lọc sau
        $assignments = \App\Models\WorkAssignment::where('UserID', $userId)
            ->where('Status', '!=', 'Draft') // Chỉ hiện lịch đã chốt/duyệt
            ->with(['shift.week', 'position'])
            ->get();

        $payrollItems = [];
        $totalHours = 0;
        $totalNightHours = 0;

        // Map thứ sang số để cộng ngày (Mon -> 0, Tue -> 1...)
        $dayMap = ['Mon' => 0, 'Tue' => 1, 'Wed' => 2, 'Thu' => 3, 'Fri' => 4, 'Sat' => 5, 'Sun' => 6];

        foreach ($assignments as $assign) {
            $weekStart = \Carbon\Carbon::parse($assign->shift->week->StartDate);
            $dayOffset = $dayMap[$assign->shift->DayOfWeek] ?? 0;
            
            // Tính ngày thực tế của ca làm
            $workDate = $weekStart->copy()->addDays($dayOffset);

            // 4. CHỈ XỬ LÝ NẾU NGÀY NẰM TRONG CHU KỲ LƯƠNG
            if ($workDate->between($startDate, $endDate)) {
                
                // Parse giờ
                $t1 = \Carbon\Carbon::parse($assign->StartTime);
                $t2 = \Carbon\Carbon::parse($assign->EndTime);
                
                // Xử lý qua đêm (Nếu End < Start nghĩa là qua ngày hôm sau)
                $isOvernight = $t2->lt($t1);
                if ($isOvernight) $t2->addDay();

                // Tổng giờ làm ca này
                $hours = abs($t2->diffInMinutes($t1)) / 60;

                // --- TÍNH GIỜ ĐÊM (22:00 - 06:00) ---
                $nightHours = 0;
                
                // Tạo các mốc thời gian đêm cho ngày hôm đó và ngày hôm sau
                // Đêm 1: 22:00 hôm nay -> 06:00 mai
                $nightStart = \Carbon\Carbon::parse($assign->StartTime)->setTime(22, 0);
                $nightEnd = $nightStart->copy()->addDay()->setTime(6, 0);

                // Ca sáng sớm: 00:00 -> 06:00 (Nếu ca bắt đầu lúc 4h sáng chẳng hạn)
                $earlyMorningStart = \Carbon\Carbon::parse($assign->StartTime)->setTime(0, 0);
                $earlyMorningEnd = \Carbon\Carbon::parse($assign->StartTime)->setTime(6, 0);

                // Logic tính giao nhau (Intersection)
                // Giao với khung 22h-6h sáng hôm sau
                $overlapStart = max($t1->timestamp, $nightStart->timestamp);
                $overlapEnd = min($t2->timestamp, $nightEnd->timestamp);
                if ($overlapEnd > $overlapStart) {
                    $nightHours += ($overlapEnd - $overlapStart) / 3600;
                }

                // Giao với khung 0h-6h sáng hôm nay (cho trường hợp ca bắt đầu lúc 3-4h sáng)
                $overlapStart2 = max($t1->timestamp, $earlyMorningStart->timestamp);
                $overlapEnd2 = min($t2->timestamp, $earlyMorningEnd->timestamp);
                if ($overlapEnd2 > $overlapStart2) {
                    $nightHours += ($overlapEnd2 - $overlapStart2) / 3600;
                }

                // 5. CỘNG DỒN (CHỈ TÍNH NẾU STATUS LÀ APPROVED)
                if ($assign->Status == 'Approved') {
                    $totalHours += $hours;
                    $totalNightHours += $nightHours;
                }

                // Lưu vào danh sách để hiển thị
                $payrollItems[] = [
                    'date' => $workDate->format('d/m/Y'),
                    'day_name' => $this->getDayName($assign->shift->DayOfWeek),
                    'time' => substr($assign->StartTime, 0, 5) . ' - ' . substr($assign->EndTime, 0, 5),
                    'position' => $assign->position->PositionName ?? '',
                    'hours' => number_format($hours, 1),
                    'night_hours' => number_format($nightHours, 1),
                    'status' => $assign->Status
                ];
            }
        }

        // Sắp xếp theo ngày tăng dần
        usort($payrollItems, function($a, $b) {
            return strtotime(str_replace('/', '-', $a['date'])) - strtotime(str_replace('/', '-', $b['date']));
        });

        // 6. TÍNH TIỀN
        $baseSalary = $totalHours * 25000;
        $nightAllowance = $totalNightHours * 25000 * 0.3;
        $totalSalary = $baseSalary + $nightAllowance;

        return view('staff.payroll', compact(
            'payrollItems', 'totalHours', 'totalNightHours', 
            'totalSalary', 'month', 'year', 'startDate', 'endDate'
        ));
    }
}