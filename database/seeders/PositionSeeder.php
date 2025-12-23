<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu có để tránh trùng lặp
        Position::truncate();

        $positions = ['Lobby', 'Counter', 'Cook', 'Prep', 'Manager'];

        foreach ($positions as $pos) {
            Position::create(['PositionName' => $pos]);
        }
    }
}