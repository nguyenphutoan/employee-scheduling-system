<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Week;
use App\Models\Shift;
use Carbon\Carbon;

class WeekShiftSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo tuần hiện tại
        $startOfWeek = Carbon::now()->startOfWeek(); 
        $endOfWeek = Carbon::now()->endOfWeek();

        $week = Week::create([
            'StartDate' => $startOfWeek->format('Y-m-d'),
            'EndDate' => $endOfWeek->format('Y-m-d'),
        ]);

        // 2. Tạo Ca làm việc (Sáng/Tối)
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        foreach ($days as $day) {
            // Tạo ca Sáng
            Shift::create([
                'WeekID' => $week->WeekID,
                'DayOfWeek' => $day,
                'ShiftType' => 'MOR'
            ]);

            // Tạo ca Tối
            Shift::create([
                'WeekID' => $week->WeekID,
                'DayOfWeek' => $day,
                'ShiftType' => 'EVE'
            ]);
        }
    }
}