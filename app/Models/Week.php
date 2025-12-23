<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Week extends Model
{
    use HasFactory;
    protected $primaryKey = 'WeekID';
    protected $fillable = ['StartDate', 'EndDate'];

    // Một tuần có nhiều ca làm việc (Shifts)
    public function shifts()
    {
        return $this->hasMany(Shift::class, 'WeekID', 'WeekID');
    }
}