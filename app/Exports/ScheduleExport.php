<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Week;
use App\Models\User;
use App\Models\WorkAssignment;
use Carbon\Carbon;

class ScheduleExport implements FromView, WithStyles, WithColumnWidths
{
    protected $weekId;

    public function __construct($weekId)
    {
        $this->weekId = $weekId;
    }

    public function view(): View
    {
        $currentWeek = Week::find($this->weekId);
        
        $start = Carbon::parse($currentWeek->StartDate);
        $daysMap = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weekDates = [];
        foreach ($daysMap as $i => $day) {
            $date = $start->copy()->addDays($i);
            $weekDates[$day] = $date->format('D d-m-y');
        }

        // Lấy User
        $users = User::where('Role', '!=', 'Manager')
            ->where('StartDate', '<=', $currentWeek->EndDate)
            ->where(function($q) use ($currentWeek) {
                $q->whereNull('EndDate')->orWhere('EndDate', '>=', $currentWeek->StartDate);
            })
            ->orderBy('UserID', 'asc')
            ->get();

        // Lấy Assignments và Eager Load
        $allAssignments = WorkAssignment::whereHas('shift', function($q) use ($currentWeek) {
            $q->where('WeekID', $currentWeek->WeekID);
        })->with(['shift', 'position'])->get();

        $groupedAssignments = $allAssignments->groupBy('UserID'); 

        $schedule = [];
        $totalHours = [];
        $dailyTotals = array_fill_keys($daysMap, 0);
        $grandTotal = 0;

        foreach ($users as $user) {
            $totalHours[$user->UserID] = 0;
            
            // Lấy danh sách ca của user này (nếu không có thì trả về collection rỗng)
            $userAssignments = $groupedAssignments->get($user->UserID, collect());

            foreach ($daysMap as $day) {
                $dayAssigns = $userAssignments->where('shift.DayOfWeek', $day);

                $cellContent = [];
                foreach ($dayAssigns as $assign) {
                    $t1 = Carbon::parse($assign->StartTime);
                    $t2 = Carbon::parse($assign->EndTime);
                    if ($t2->lt($t1)) $t2->addDay();
                    $hours = abs($t2->diffInMinutes($t1)) / 60;
                    
                    $totalHours[$user->UserID] += $hours;
                    $dailyTotals[$day] += $hours;
                    $grandTotal += $hours;

                    $pos = $assign->position->PositionName ?? '';
                    $startT = substr($assign->StartTime, 0, 5);
                    $endT = substr($assign->EndTime, 0, 5);
                    
                    $cellContent[] = "$startT - $endT $pos"; 
                }
                $schedule[$user->UserID][$day] = implode("\n", $cellContent);
            }
        }

        return view('exports.schedule', [
            'users' => $users,
            'weekDates' => $weekDates,
            'schedule' => $schedule,
            'totalHours' => $totalHours,
            'dailyTotals' => $dailyTotals,
            'grandTotal' => $grandTotal,
            'daysMap' => $daysMap
        ]);
    }

    // 2. Set độ rộng cột cố định
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Cột Mã NV
            'B' => 25, // Cột Tên NV
            'C' => 20, // Mon
            'D' => 20, // Tue
            'E' => 20, // Wed
            'F' => 20, // Thu
            'G' => 20, // Fri
            'H' => 20, // Sat
            'I' => 20, // Sun
            'J' => 15, // Total
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setVertical('center');

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}