<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'late_minutes',
        'overtime_minutes',
        'notes'
    ];

    protected $dates = [
        'date',
        'clock_in',
        'clock_out'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Calculate working hours
    public function getWorkingHoursAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $clockIn = \Carbon\Carbon::parse($this->clock_in);
        $clockOut = \Carbon\Carbon::parse($this->clock_out);
        
        return round($clockOut->diffInMinutes($clockIn) / 60, 2);
    }

    // Check if employee is late
    public function calculateLateMinutes($expectedTime = '09:00')
    {
        if (!$this->clock_in) {
            return 0;
        }

        $clockIn = \Carbon\Carbon::parse($this->clock_in);
        $expectedClockIn = \Carbon\Carbon::parse($expectedTime);

        return $clockIn > $expectedClockIn ? $clockIn->diffInMinutes($expectedClockIn) : 0;
    }

    // Calculate overtime
    public function calculateOvertimeMinutes($standardHours = 8)
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $workingMinutes = \Carbon\Carbon::parse($this->clock_in)->diffInMinutes(\Carbon\Carbon::parse($this->clock_out));
        $standardMinutes = $standardHours * 60;

        return $workingMinutes > $standardMinutes ? $workingMinutes - $standardMinutes : 0;
    }
}
