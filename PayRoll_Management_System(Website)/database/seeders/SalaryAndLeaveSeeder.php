<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalaryAndLeaveSeeder extends Seeder
{
    public function run()
    {
        $currentDate = Carbon::now();
        $employees = range(1, 5);

        // Insert salary records for the last 3 months
        foreach ($employees as $employeeId) {
            for ($i = 0; $i < 3; $i++) {
                $date = $currentDate->copy()->subMonths($i);
                $baseSalary = DB::table('employees')->where('id', $employeeId)->value('base_salary');
                
                // Calculate random overtime and bonus
                $overtimePay = rand(5000, 15000);
                $bonus = rand(10000, 30000);
                $deductions = rand(2000, 5000);
                
                DB::table('salaries')->insert([
                    'employee_id' => $employeeId,
                    'salary_date' => $date->format('Y-m-d'),
                    'base_salary' => $baseSalary,
                    'overtime_pay' => $overtimePay,
                    'bonus' => $bonus,
                    'deductions' => $deductions,
                    'net_salary' => $baseSalary + $overtimePay + $bonus - $deductions,
                    'payment_status' => 'paid',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // Insert leave request records
        $leaveTypes = ['annual', 'sick', 'personal', 'maternity', 'paternity', 'unpaid'];
        $statuses = ['approved', 'pending', 'rejected'];

        foreach ($employees as $employeeId) {
            // Create 2 leave requests per employee
            for ($i = 0; $i < 2; $i++) {
                $startDate = $currentDate->copy()->addDays(rand(1, 30));
                $duration = rand(1, 5);
                $endDate = $startDate->copy()->addDays($duration - 1);

                DB::table('leave_requests')->insert([
                    'employee_id' => $employeeId,
                    'type' => $leaveTypes[array_rand($leaveTypes)],
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'total_days' => $duration,
                    'reason' => 'Sample leave request reason',
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // Insert more varied attendance records
        $statuses = ['present', 'absent', 'late', 'on_leave'];
        
        foreach ($employees as $employeeId) {
            for ($i = 0; $i < 30; $i++) {
                $date = $currentDate->copy()->subDays($i);
                $status = $statuses[array_rand($statuses)];
                
                $lateMinutes = ($status === 'late') ? rand(1, 60) : 0;
                $overtimeMinutes = ($status === 'present') ? rand(0, 120) : 0;
                
                DB::table('attendance')->insert([
                    'employee_id' => $employeeId,
                    'date' => $date->format('Y-m-d'),
                    'clock_in' => $status !== 'absent' ? $date->copy()->setHour(9)->addMinutes($lateMinutes)->format('H:i:s') : null,
                    'clock_out' => $status !== 'absent' ? $date->copy()->setHour(17)->addMinutes($overtimeMinutes)->format('H:i:s') : null,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
} 