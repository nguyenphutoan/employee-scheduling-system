<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAssignment extends Model
{
    use HasFactory;
    protected $primaryKey = 'WaID';
    protected $fillable = ['ShiftID', 'UserID', 'PositionID', 'StartTime', 'EndTime', 'Status'];

    // Phân công này thuộc về User nào
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    // Thuộc ca nào
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'ShiftID', 'ShiftID');
    }

    // Làm vị trí gì
    public function position()
    {
        return $this->belongsTo(Position::class, 'PositionID', 'PositionID');
    }
}