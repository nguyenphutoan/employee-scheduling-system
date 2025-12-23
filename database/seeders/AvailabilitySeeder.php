<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmpAvailability;
use App\Models\User;
use App\Models\Week;
use Carbon\Carbon;

class AvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ để tránh trùng
        EmpAvailability::truncate();

        $week = Week::first();
        
        if (!$week) {
            $this->command->info('Chưa có dữ liệu Tuần! Hãy chạy WeekShiftSeeder trước.');
            return;
        }

        // 2. Lấy 2 nhân viên Staff
        $staff1 = User::where('UserName', 'staff1')->first();
        $staff2 = User::where('UserName', 'staff2')->first();

        // Danh sách các ngày trong tuần
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        foreach ($days as $day) {
            
            // --- STAFF 1: RẢNH BUỔI SÁNG (07:00 - 12:00) ---
            if ($staff1) {
                EmpAvailability::create([
                    'UserID' => $staff1->UserID,
                    'WeekID' => $week->WeekID,
                    'DayOfWeek' => $day,
                    'AvailableFrom' => '07:00:00',
                    'AvailableTo' => '12:00:00',
                ]);
            }

            // --- STAFF 2: RẢNH BUỔI CHIỀU TỐI (13:00 - 22:00) ---
            if ($staff2) {
                EmpAvailability::create([
                    'UserID' => $staff2->UserID,
                    'WeekID' => $week->WeekID,
                    'DayOfWeek' => $day,
                    'AvailableFrom' => '13:00:00',
                    'AvailableTo' => '22:00:00',
                ]);
            }
        }
    }
}