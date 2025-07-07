<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'hire_date',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'department_id',
        'position',
        'base_salary',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'base_salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the department that the employee belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the salaries for the employee.
     */
    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * Get the attendance records for the employee.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leave requests for the employee.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the managed departments for the employee.
     */
    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the employee's age.
     */
    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    /**
     * Get the employee's years of service.
     */
    public function getYearsOfServiceAttribute(): int
    {
        return Carbon::parse($this->hire_date)->diffInYears(Carbon::now());
    }

    /**
     * Get the employee's current month attendance percentage.
     */
    public function getCurrentMonthAttendancePercentageAttribute(): float
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $workingDays = Carbon::now()->daysInMonth - 8; // Excluding weekends (approximation)
        $presentDays = $this->attendances()
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'present')
            ->count();

        return $workingDays > 0 ? ($presentDays / $workingDays) * 100 : 0;
    }

    /**
     * Get the employee's latest salary.
     */
    public function getLatestSalaryAttribute(): ?Salary
    {
        return $this->salaries()->latest()->first();
    }
}
