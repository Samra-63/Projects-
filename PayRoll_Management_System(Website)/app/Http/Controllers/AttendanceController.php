<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Attendance::query();
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->whereHas('employee', function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'LIKE', "%{$searchTerm}%");
                });
        }

        $attendances = $query->with('employee')
                            ->orderBy('date', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();

        return view('attendance.index', compact('attendances'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $employees = Employee::orderBy('first_name')->get();
            return view('attendance.create', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Error loading create attendance form: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error loading the form. Please try again.');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'clock_in' => 'nullable|date_format:H:i',
                'clock_out' => 'nullable|date_format:H:i|after:clock_in',
                'status' => 'required|in:present,absent,late,half_day,on_leave',
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Check for existing attendance
            $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->first();

            if ($existingAttendance) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Attendance record already exists for this employee on the selected date.');
            }

            // Calculate late minutes and overtime if clocked in
            $lateMinutes = 0;
            $overtimeMinutes = 0;
            
            if ($validated['clock_in'] && $validated['status'] !== 'absent') {
                $attendance = new Attendance();
                $lateMinutes = $attendance->calculateLateMinutes($validated['clock_in']);
                
                if ($validated['clock_out']) {
                    $overtimeMinutes = $attendance->calculateOvertimeMinutes(
                        Carbon::parse($validated['clock_in'])->diffInHours(Carbon::parse($validated['clock_out']))
                    );
                }
            }

            Attendance::create([
                'employee_id' => $validated['employee_id'],
                'date' => $validated['date'],
                'clock_in' => $validated['clock_in'],
                'clock_out' => $validated['clock_out'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'late_minutes' => $lateMinutes,
                'overtime_minutes' => $overtimeMinutes
            ]);

            DB::commit();

            return redirect()->route('attendance.index')
                ->with('success', 'Attendance record created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating attendance record: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating attendance record. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Attendance $attendance)
    {
        try {
            $attendance->load('employee');
            return view('attendance.show', compact('attendance'));
        } catch (\Exception $e) {
            Log::error('Error showing attendance details: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error loading attendance details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Attendance $attendance)
    {
        try {
            $employees = Employee::orderBy('first_name')->get();
            return view('attendance.edit', compact('attendance', 'employees'));
        } catch (\Exception $e) {
            Log::error('Error loading edit attendance form: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error loading the form. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Attendance $attendance)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'clock_in' => 'nullable|date_format:H:i',
                'clock_out' => 'nullable|date_format:H:i|after:clock_in',
                'status' => 'required|in:present,absent,late,half_day,on_leave',
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Check for existing attendance (excluding current record)
            $existingAttendance = Attendance::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->where('id', '!=', $attendance->id)
                ->first();

            if ($existingAttendance) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Attendance record already exists for this employee on the selected date.');
            }

            // Calculate late minutes and overtime if clocked in
            $lateMinutes = 0;
            $overtimeMinutes = 0;
            
            if ($validated['clock_in'] && $validated['status'] !== 'absent') {
                $lateMinutes = $attendance->calculateLateMinutes($validated['clock_in']);
                
                if ($validated['clock_out']) {
                    $overtimeMinutes = $attendance->calculateOvertimeMinutes(
                        Carbon::parse($validated['clock_in'])->diffInHours(Carbon::parse($validated['clock_out']))
                    );
                }
            }

            $attendance->update([
                'employee_id' => $validated['employee_id'],
                'date' => $validated['date'],
                'clock_in' => $validated['clock_in'],
                'clock_out' => $validated['clock_out'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'late_minutes' => $lateMinutes,
                'overtime_minutes' => $overtimeMinutes
            ]);

            DB::commit();

            return redirect()->route('attendance.index')
                ->with('success', 'Attendance record updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating attendance record: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating attendance record. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Attendance $attendance)
    {
        try {
            DB::beginTransaction();
            $attendance->delete();
            DB::commit();

            return redirect()->route('attendance.index')
                ->with('success', 'Attendance record deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting attendance record: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting attendance record. Please try again.');
        }
    }

    public function report(Request $request)
    {
        try {
            $month = $request->get('month', date('Y-m'));
            $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            // Get monthly statistics with proper MySQL date formatting
            $monthlyStats = DB::table('attendance')
                ->select(
                    DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as total_records'),
                    DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as total_present'),
                    DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as total_absent'),
                    DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as total_late'),
                    DB::raw('SUM(CASE WHEN status = "half_day" THEN 1 ELSE 0 END) as total_half_day'),
                    DB::raw('SUM(CASE WHEN status = "on_leave" THEN 1 ELSE 0 END) as total_on_leave'),
                    DB::raw('SUM(late_minutes) as total_late_minutes'),
                    DB::raw('SUM(overtime_minutes) as total_overtime_minutes')
                )
                ->groupBy(DB::raw('DATE_FORMAT(date, "%Y-%m")'))
                ->orderBy('month', 'desc')
                ->get();

            // Get employee statistics for the selected month
            $employeeStats = DB::table('attendance')
                ->join('employees', 'attendance.employee_id', '=', 'employees.id')
                ->select(
                    'employees.id',
                    'employees.first_name',
                    'employees.last_name',
                    DB::raw('COUNT(*) as total_records'),
                    DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as total_present'),
                    DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as total_absent'),
                    DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as total_late'),
                    DB::raw('SUM(late_minutes) as total_late_minutes'),
                    DB::raw('SUM(overtime_minutes) as total_overtime_minutes')
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('employees.id', 'employees.first_name', 'employees.last_name')
                ->get();

            if ($monthlyStats->isEmpty() && $employeeStats->isEmpty()) {
                Log::info('No attendance records found for the period: ' . $month);
            }

            return view('attendance.report', compact('monthlyStats', 'employeeStats', 'month'));
        } catch (\Exception $e) {
            Log::error('Error generating attendance report: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Error generating attendance report. Please try again.');
        }
    }
}
