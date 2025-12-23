<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpAvailability extends Model
{
    use HasFactory;
    protected $primaryKey = 'EaID';
    protected $fillable = ['UserID', 'WeekID', 'DayOfWeek', 'AvailableFrom', 'AvailableTo'];

    public function user()
    {
        // Khai báo quan hệ: Lịch rảnh này thuộc về User (dựa vào UserID)
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }
}