<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Salary;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function getReportData(Request $request)
    {
        try {
            $month = $request->get('month', date('Y-m'));
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            // Get summary data
            $summary = $this->getSummaryData($startDate, $endDate);

            // Get chart data
            $charts = $this->getChartData($startDate, $endDate);

            // Get detailed reports
            $details = $this->getDetailedReports($startDate, $endDate);

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'charts' => $charts,
                'details' => $details
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generating report data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate report data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getSummaryData($startDate, $endDate)
    {
        try {
            // Total salary disbursed
            $totalSalary = Salary::whereBetween('salary_date', [$startDate, $endDate])
                ->sum('net_salary') ?? 0;

            // Total working days
            $totalWorkingDays = $endDate->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $startDate) + 1;

            // Total attendance
            $totalAttendance = Attendance::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'present')
                ->count();

            // Employee count
            $employeeCount = Employee::where('hire_date', '<=', $endDate)
                ->where('status', 'active')
                ->count();

            // Attendance rate
            $attendanceRate = ($totalWorkingDays > 0 && $employeeCount > 0) 
                ? round(($totalAttendance / ($totalWorkingDays * $employeeCount)) * 100, 2)
                : 0;

            // Total overtime hours
            $overtimeHours = Attendance::whereBetween('date', [$startDate, $endDate])
                ->sum('overtime_minutes') / 60;

            // Total leave requests
            $leaveRequests = LeaveRequest::where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->count();

            return [
                'totalSalary' => $totalSalary,
                'attendanceRate' => $attendanceRate,
                'overtimeHours' => round($overtimeHours, 2),
                'leaveRequests' => $leaveRequests
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getSummaryData: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getChartData($startDate, $endDate)
    {
        try {
            // Salary distribution by department
            $salaryByDepartment = DB::table('salaries')
                ->join('employees', 'salaries.employee_id', '=', 'employees.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->whereBetween('salaries.salary_date', [$startDate, $endDate])
                ->groupBy('departments.id', 'departments.name')
                ->select(
                    'departments.name',
                    DB::raw('SUM(salaries.net_salary) as total_salary')
                )
                ->get();

            // Attendance overview
            $attendanceOverview = Attendance::whereBetween('date', [$startDate, $endDate])
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Ensure all status types are represented
            $statuses = ['present', 'absent', 'late', 'on_leave'];
            $attendanceCounts = array_map(function($status) use ($attendanceOverview) {
                return $attendanceOverview[$status] ?? 0;
            }, $statuses);

            return [
                'salary' => [
                    'labels' => $salaryByDepartment->pluck('name')->toArray(),
                    'data' => $salaryByDepartment->pluck('total_salary')->toArray()
                ],
                'attendance' => $attendanceCounts
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getChartData: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getDetailedReports($startDate, $endDate)
    {
        try {
            // Salary details
            $salaryDetails = DB::table('salaries')
                ->join('employees', 'salaries.employee_id', '=', 'employees.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->whereBetween('salaries.salary_date', [$startDate, $endDate])
                ->select(
                    DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as employee"),
                    'departments.name as department',
                    'salaries.base_salary',
                    'salaries.overtime_pay as overtime',
                    'salaries.bonus',
                    'salaries.deductions',
                    'salaries.net_salary'
                )
                ->get();

            // Attendance details
            $attendanceDetails = DB::table('attendance')
                ->join('employees', 'attendance.employee_id', '=', 'employees.id')
                ->whereBetween('attendance.date', [$startDate, $endDate])
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
                ->select(
                    DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as employee"),
                    DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days'),
                    DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days'),
                    DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_days'),
                    DB::raw('SUM(CASE WHEN status = "on_leave" THEN 1 ELSE 0 END) as leave_days'),
                    DB::raw('SUM(overtime_minutes) / 60 as overtime_hours'),
                    DB::raw('(SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) / COUNT(*) * 100) as attendance_rate')
                )
                ->get();

            // Leave details
            $leaveDetails = DB::table('leave_requests')
                ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate]);
                })
                ->select(
                    DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as employee"),
                    'leave_requests.type as leave_type',
                    'leave_requests.start_date',
                    'leave_requests.end_date',
                    DB::raw('DATEDIFF(end_date, start_date) + 1 as days'),
                    'leave_requests.status',
                    'leave_requests.reason'
                )
                ->orderBy('start_date', 'desc')
                ->get();

            return [
                'salary' => $salaryDetails,
                'attendance' => $attendanceDetails,
                'leave' => $leaveDetails
            ];
        } catch (\Exception $e) {
            \Log::error('Error in getDetailedReports: ' . $e->getMessage());
            throw $e;
        }
    }

    public function exportReport(Request $request)
    {
        try {
            $month = $request->get('month', date('Y-m'));
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            // Get report data
            $summary = $this->getSummaryData($startDate, $endDate);
            $details = $this->getDetailedReports($startDate, $endDate);

            // Create Excel file
            $spreadsheet = new Spreadsheet();
            
            // Summary Sheet
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Summary');
            
            $sheet->setCellValue('A1', 'Summary Report for ' . $startDate->format('F Y'));
            $sheet->setCellValue('A3', 'Total Salary Disbursed:');
            $sheet->setCellValue('B3', '$' . number_format($summary['totalSalary'], 2));
            $sheet->setCellValue('A4', 'Attendance Rate:');
            $sheet->setCellValue('B4', $summary['attendanceRate'] . '%');
            $sheet->setCellValue('A5', 'Total Overtime Hours:');
            $sheet->setCellValue('B5', number_format($summary['overtimeHours'], 1));
            $sheet->setCellValue('A6', 'Total Leave Requests:');
            $sheet->setCellValue('B6', $summary['leaveRequests']);

            // Salary Details Sheet
            $spreadsheet->createSheet();
            $spreadsheet->setActiveSheetIndex(1);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Salary Details');

            $headers = ['Employee', 'Department', 'Base Salary', 'Overtime', 'Bonus', 'Deductions', 'Net Salary'];
            $sheet->fromArray($headers, NULL, 'A1');

            $row = 2;
            foreach ($details['salary'] as $salary) {
                $sheet->fromArray([
                    $salary->employee,
                    $salary->department,
                    $salary->base_salary,
                    $salary->overtime,
                    $salary->bonus,
                    $salary->deductions,
                    $salary->net_salary
                ], NULL, 'A' . $row);
                $row++;
            }

            // Attendance Details Sheet
            $spreadsheet->createSheet();
            $spreadsheet->setActiveSheetIndex(2);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Attendance Details');

            $headers = ['Employee', 'Present Days', 'Absent Days', 'Late Days', 'Leave Days', 'Overtime Hours', 'Attendance Rate'];
            $sheet->fromArray($headers, NULL, 'A1');

            $row = 2;
            foreach ($details['attendance'] as $attendance) {
                $sheet->fromArray([
                    $attendance->employee,
                    $attendance->present_days,
                    $attendance->absent_days,
                    $attendance->late_days,
                    $attendance->leave_days,
                    $attendance->overtime_hours,
                    $attendance->attendance_rate . '%'
                ], NULL, 'A' . $row);
                $row++;
            }

            // Leave Details Sheet
            $spreadsheet->createSheet();
            $spreadsheet->setActiveSheetIndex(3);
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Leave Details');

            $headers = ['Employee', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Status', 'Reason'];
            $sheet->fromArray($headers, NULL, 'A1');

            $row = 2;
            foreach ($details['leave'] as $leave) {
                $sheet->fromArray([
                    $leave->employee,
                    $leave->leave_type,
                    $leave->start_date,
                    $leave->end_date,
                    $leave->days,
                    $leave->status,
                    $leave->reason
                ], NULL, 'A' . $row);
                $row++;
            }

            // Set first sheet as active
            $spreadsheet->setActiveSheetIndex(0);

            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            $filename = 'payroll_report_' . $month . '.xlsx';
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            \Log::error('Error exporting report: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error exporting report. Please try again.');
        }
    }

    public function exportSalaryReport(Request $request)
    {
        try {
            $month = $request->get('month', date('Y-m'));
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            $salaryDetails = DB::table('salaries')
                ->join('employees', 'salaries.employee_id', '=', 'employees.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->whereBetween('salaries.salary_date', [$startDate, $endDate])
                ->select(
                    DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as Employee"),
                    'departments.name as Department',
                    'salaries.base_salary as Base_Salary',
                    'salaries.overtime_pay as Overtime',
                    'salaries.bonus as Bonus',
                    'salaries.deductions as Deductions',
                    'salaries.net_salary as Net_Salary'
                )
                ->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=salary_report_' . $month . '.csv',
            ];

            $callback = function() use ($salaryDetails) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, ['Employee', 'Department', 'Base Salary', 'Overtime', 'Bonus', 'Deductions', 'Net Salary']);
                
                // Add data rows
                foreach ($salaryDetails as $row) {
                    fputcsv($file, [
                        $row->Employee,
                        $row->Department,
                        '₨' . number_format($row->Base_Salary, 2),
                        '₨' . number_format($row->Overtime, 2),
                        '₨' . number_format($row->Bonus, 2),
                        '₨' . number_format($row->Deductions, 2),
                        '₨' . number_format($row->Net_Salary, 2)
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Error exporting salary report: ' . $e->getMessage());
            return back()->with('error', 'Failed to export salary report: ' . $e->getMessage());
        }
    }

    public function exportAttendanceReport(Request $request)
    {
        try {
            $month = $request->get('month', date('Y-m'));
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            $attendanceDetails = DB::table('attendance')
                ->join('employees', 'attendance.employee_id', '=', 'employees.id')
                ->whereBetween('attendance.date', [$startDate, $endDate])
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
                ->select(
                    DB::raw("CONCAT(employees.first_name, ' ', employees.last_name) as Employee"),
                    DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as Present_Days'),
                    DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as Absent_Days'),
                    DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as Late_Days'),
                    DB::raw('SUM(CASE WHEN status = "on_leave" THEN 1 ELSE 0 END) as Leave_Days'),
                    DB::raw('SUM(overtime_minutes) / 60 as Overtime_Hours'),
                    DB::raw('(SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) / COUNT(*) * 100) as Attendance_Rate')
                )
                ->get();

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename=attendance_report_' . $month . '.csv',
            ];

            $callback = function() use ($attendanceDetails) {
                $file = fopen('php://output', 'w');
                
                // Add headers
                fputcsv($file, ['Employee', 'Present Days', 'Absent Days', 'Late Days', 'Leave Days', 'Overtime Hours', 'Attendance Rate']);
                
                // Add data rows
                foreach ($attendanceDetails as $row) {
                    fputcsv($file, [
                        $row->Employee,
                        $row->Present_Days,
                        $row->Absent_Days,
                        $row->Late_Days,
                        $row->Leave_Days,
                        number_format($row->Overtime_Hours, 2),
                        number_format($row->Attendance_Rate, 2) . '%'
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Error exporting attendance report: ' . $e->getMessage());
            return back()->with('error', 'Failed to export attendance report: ' . $e->getMessage());
        }
    }
} 