<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo tài khoản Quản lý (Admin)
        User::create([
            'UserName' => 'admin',
            'FullName' => 'Nguyễn Phú Toàn',
            'email' => 'admin@toan.com',
            'password' => Hash::make('123456'), 
            'StartDate' => '2023-01-01',
            'Role' => 'Manager'
        ]);

        // 2. Tạo nhân viên 1
        User::create([
            'UserName' => 'staff1',
            'FullName' => 'Trần Nhân Viên',
            'email' => 'staff1@example.com',
            'password' => Hash::make('123456'),
            'StartDate' => '2023-05-10',
            'Role' => 'Staff'
        ]);

        // 3. Tạo nhân viên 2
        User::create([
            'UserName' => 'staff2',
            'FullName' => 'Lê Phụ Bếp',
            'email' => 'staff2@example.com',
            'password' => Hash::make('123456'),
            'StartDate' => '2023-06-20',
            'Role' => 'Staff'
        ]);
    }
}