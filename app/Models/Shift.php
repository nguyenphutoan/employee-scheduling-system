<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;
    protected $primaryKey = 'ShiftID';
    protected $fillable = ['WeekID', 'DayOfWeek', 'ShiftType'];

    // Ca làm việc thuộc về một Tuần
    public function week()
    {
        return $this->belongsTo(Week::class, 'WeekID', 'WeekID');
    }

    // Một ca có nhiều người làm (WorkAssignment)
    public function assignments()
    {
        return $this->hasMany(WorkAssignment::class, 'ShiftID', 'ShiftID');
    }
}