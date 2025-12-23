<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'UserID'; 

    protected $fillable = [
        'UserName', 'FullName', 'email', 'password', 'StartDate', 'EndDate', 'Role'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Một nhân viên có nhiều phân công công việc
    public function assignments()
    {
        return $this->hasMany(WorkAssignment::class, 'UserID', 'UserID');
    }

    // Một nhân viên có nhiều lịch đăng ký rảnh
    public function availabilities()
    {
        return $this->hasMany(EmpAvailability::class, 'UserID', 'UserID');
    }
}