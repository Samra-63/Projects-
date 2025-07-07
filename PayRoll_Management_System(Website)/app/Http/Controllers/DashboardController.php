<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        try {
            $data = [
                'totalEmployees' => $this->getTotalEmployees(),
                'totalDepartments' => $this->getTotalDepartments(),
                'presentToday' => $this->getPresentEmployeesToday(),
                'pendingLeaves' => $this->getPendingLeaves(),
                'recentActivities' => $this->getRecentActivities(),
                'departmentStats' => $this->getDepartmentStats(),
                'salaryStats' => $this->getSalaryStats(),
            ];

            return view('dashboard', $data);
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
            return view('dashboard', [
                'totalEmployees' => 0,
                'totalDepartments' => 0,
                'presentToday' => 0,
                'pendingLeaves' => 0,
                'recentActivities' => collect(),
                'departmentStats' => collect(),
                'salaryStats' => [],
                'error' => 'Error loading dashboard data.'
            ]);
        }
    }

    /**
     * Get total number of employees
     *
     * @return int
     */
    protected function getTotalEmployees(): int
    {
        return Employee::count();
    }

    /**
     * Get total number of departments
     *
     * @return int
     */
    protected function getTotalDepartments(): int
    {
        return Department::count();
    }

    /**
     * Get number of employees present today
     *
     * @return int
     */
    protected function getPresentEmployeesToday(): int
    {
        return Attendance::whereDate('date', Carbon::today())
            ->where('status', 'present')
            ->count();
    }

    /**
     * Get number of pending leave requests
     *
     * @return int
     */
    protected function getPendingLeaves(): int
    {
        return LeaveRequest::where('status', 'pending')->count();
    }

    /**
     * Get recent activities across the system
     *
     * @return Collection
     */
    protected function getRecentActivities(): Collection
    {
        try {
            // Get recent employees
            $recentEmployees = Employee::with('department')
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($employee) {
                    return [
                        'type' => 'employee',
                        'title' => 'New Employee Added',
                        'description' => "{$employee->first_name} {$employee->last_name} joined {$employee->department->name}",
                        'created_at' => $employee->created_at
                    ];
                });

            // Get recent leave requests
            $recentLeaves = LeaveRequest::with('employee')
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($leave) {
                    return [
                        'type' => 'leave',
                        'title' => 'Leave Request ' . ucfirst($leave->status),
                        'description' => "{$leave->employee->first_name} {$leave->employee->last_name}'s leave request",
                        'created_at' => $leave->updated_at
                    ];
                });

            // Get recent salary records
            $recentSalaries = Salary::with('employee')
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($salary) {
                    return [
                        'type' => 'salary',
                        'title' => 'Salary Processed',
                        'description' => "Processed for {$salary->employee->first_name} {$salary->employee->last_name}",
                        'created_at' => $salary->created_at
                    ];
                });

            // Get recent attendance records
            $recentAttendance = Attendance::with('employee')
                ->latest()
                ->take(3)
                ->get()
                ->map(function ($attendance) {
                    return [
                        'type' => 'attendance',
                        'title' => ucfirst($attendance->status),
                        'description' => "{$attendance->employee->first_name} {$attendance->employee->last_name} marked as {$attendance->status}",
                        'created_at' => $attendance->created_at
                    ];
                });

            // Merge all activities and sort by date
            return collect()
                ->merge($recentEmployees)
                ->merge($recentLeaves)
                ->merge($recentSalaries)
                ->merge($recentAttendance)
                ->sortByDesc('created_at')
                ->take(5)
                ->values();

        } catch (\Exception $e) {
            Log::error('Error getting recent activities: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get department statistics
     *
     * @return Collection
     */
    protected function getDepartmentStats(): Collection
    {
        try {
            return Department::withCount('employees')
                ->get()
                ->map(function ($department) {
                    return [
                        'name' => $department->name,
                        'employee_count' => $department->employees_count,
                        'budget' => $department->budget ?? 0
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting department stats: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get salary statistics
     *
     * @return array
     */
    protected function getSalaryStats(): array
    {
        try {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $salaries = Salary::whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear);

            return [
                'total_payroll' => $salaries->sum('net_salary') ?? 0,
                'average_salary' => $salaries->avg('net_salary') ?? 0,
                'highest_salary' => $salaries->max('net_salary') ?? 0,
                'month' => Carbon::now()->format('F Y')
            ];
        } catch (\Exception $e) {
            Log::error('Error getting salary stats: ' . $e->getMessage());
            return [
                'total_payroll' => 0,
                'average_salary' => 0,
                'highest_salary' => 0,
                'month' => Carbon::now()->format('F Y')
            ];
        }
    }
}