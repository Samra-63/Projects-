<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Salary::query();
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->whereHas('employee', function($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'LIKE', "%{$searchTerm}%");
            })
            ->orWhere('base_salary', 'LIKE', "%{$searchTerm}%")
            ->orWhere('payment_status', 'LIKE', "%{$searchTerm}%");
        }

        $salaries = $query->with('employee')
                         ->orderBy('created_at', 'desc')
                         ->get();

        return view('salaries.index', compact('salaries'));
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
            return view('salaries.create', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Error loading create salary form: ' . $e->getMessage());
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
                'base_salary' => 'required|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'overtime_rate' => 'nullable|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
                'payment_method' => 'required|in:bank_transfer,cash,check',
                'payment_status' => 'required|in:pending,processing,paid,cancelled',
                'salary_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Create a new salary instance
            $salary = new Salary($validated);
            
            // Calculate overtime pay if hours and rate are provided
            if ($validated['overtime_hours'] && $validated['overtime_rate']) {
                $salary->overtime_pay = $salary->calculateOvertimePay();
            }

            // Calculate tax and net salary
            $salary->tax = $salary->calculateTax();
            $salary->net_salary = $salary->calculateNetSalary();

            // Save the salary record
            $salary->save();

            DB::commit();

            return redirect()->route('salaries.index')
                ->with('success', 'Salary record created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating salary record: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating salary record. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Salary $salary)
    {
        try {
            $salary->load('employee');
            return view('salaries.show', compact('salary'));
        } catch (\Exception $e) {
            Log::error('Error showing salary details: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error loading salary details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Salary $salary)
    {
        try {
            $employees = Employee::orderBy('first_name')->get();
            return view('salaries.edit', compact('salary', 'employees'));
        } catch (\Exception $e) {
            Log::error('Error loading edit salary form: ' . $e->getMessage());
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
    public function update(Request $request, Salary $salary)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'base_salary' => 'required|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'overtime_rate' => 'nullable|numeric|min:0',
                'bonus' => 'nullable|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'deductions' => 'nullable|numeric|min:0',
                'payment_method' => 'required|in:bank_transfer,cash,check',
                'payment_status' => 'required|in:pending,processing,paid,cancelled',
                'salary_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Fill the salary model with new values
            $salary->fill($validated);

            // Calculate overtime pay if hours and rate are provided
            if ($validated['overtime_hours'] && $validated['overtime_rate']) {
                $salary->overtime_pay = $salary->calculateOvertimePay();
            }

            // Calculate tax and net salary
            $salary->tax = $salary->calculateTax();
            $salary->net_salary = $salary->calculateNetSalary();

            // Save the updated salary record
            $salary->save();

            DB::commit();

            return redirect()->route('salaries.index')
                ->with('success', 'Salary record updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating salary record: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating salary record. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Salary $salary)
    {
        try {
            DB::beginTransaction();
            $salary->delete();
            DB::commit();

            return redirect()->route('salaries.index')
                ->with('success', 'Salary record deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting salary record: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting salary record. Please try again.');
        }
    }

    /**
     * Display salary reports.
     *
     * @return \Illuminate\Http\Response
     */
    public function report()
    {
        try {
            // Get monthly totals
            $monthlyTotals = DB::table('salaries')
                ->select(
                    DB::raw('DATE_FORMAT(salary_date, "%Y-%m") as month'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('SUM(base_salary) as total_base'),
                    DB::raw('SUM(overtime_pay) as total_overtime'),
                    DB::raw('SUM(bonus) as total_bonus'),
                    DB::raw('SUM(allowances) as total_allowances'),
                    DB::raw('SUM(deductions) as total_deductions'),
                    DB::raw('SUM(tax) as total_tax'),
                    DB::raw('SUM(net_salary) as total_net')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get();

            // Get department totals
            $departmentTotals = DB::table('salaries')
                ->join('employees', 'salaries.employee_id', '=', 'employees.id')
                ->join('departments', 'employees.department_id', '=', 'departments.id')
                ->select(
                    'departments.name as department',
                    DB::raw('COUNT(DISTINCT salaries.employee_id) as employee_count'),
                    DB::raw('SUM(salaries.net_salary) as total_net_salary'),
                    DB::raw('CASE 
                        WHEN COUNT(DISTINCT salaries.employee_id) > 0 
                        THEN SUM(salaries.net_salary) / COUNT(DISTINCT salaries.employee_id)
                        ELSE 0 
                    END as avg_salary')
                )
                ->groupBy('departments.id', 'departments.name')
                ->get();

            // Log the data for debugging
            \Log::info('Monthly Totals:', ['data' => $monthlyTotals]);
            \Log::info('Department Totals:', ['data' => $departmentTotals]);

            return view('salaries.report', compact('monthlyTotals', 'departmentTotals'));
        } catch (\Exception $e) {
            \Log::error('Error generating salary report: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Error generating salary report. Please try again. Error: ' . $e->getMessage());
        }
    }
}
