<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Employee::query();
        
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('position', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'LIKE', "%{$searchTerm}%");
            });
        }

        $employees = $query->orderBy('created_at', 'desc')->get();

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            $departments = Department::orderBy('name')->get();
            return view('employees.create', compact('departments'));
        } catch (\Exception $e) {
            Log::error('Error loading create employee form: ' . $e->getMessage());
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
                'employee_id' => 'required|unique:employees,employee_id',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:employees,email',
                'phone' => 'required|string|max:20',
                'date_of_birth' => 'required|date|before:today',
                'hire_date' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:255',
                'department_id' => 'required|exists:departments,id',
                'position' => 'required|string|max:255',
                'base_salary' => 'required|numeric|min:0',
                'status' => 'required|in:active,on_leave,terminated'
            ]);

            DB::beginTransaction();
            $employee = Employee::create($validated);
            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating employee. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        try {
            $employee->load(['department', 'salaries' => function($query) {
                $query->latest()->take(5);
            }, 'attendances' => function($query) {
                $query->latest()->take(5);
            }, 'leaveRequests' => function($query) {
                $query->latest()->take(5);
            }]);

            return view('employees.show', compact('employee'));
        } catch (\Exception $e) {
            Log::error('Error showing employee details: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error loading employee details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        try {
            $departments = Department::orderBy('name')->get();
            return view('employees.edit', compact('employee', 'departments'));
        } catch (\Exception $e) {
            Log::error('Error loading edit employee form: ' . $e->getMessage());
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
    public function update(Request $request, Employee $employee)
    {
        try {
            $validated = $request->validate([
                'employee_id' => ['required', Rule::unique('employees')->ignore($employee)],
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('employees')->ignore($employee)],
                'phone' => 'required|string|max:20',
                'date_of_birth' => 'required|date|before:today',
                'hire_date' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'required|string|max:20',
                'country' => 'required|string|max:255',
                'department_id' => 'required|exists:departments,id',
                'position' => 'required|string|max:255',
                'base_salary' => 'required|numeric|min:0',
                'status' => 'required|in:active,on_leave,terminated'
            ]);

            DB::beginTransaction();
            $employee->update($validated);
            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating employee. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        try {
            DB::beginTransaction();
            $employee->delete();
            DB::commit();

            return redirect()->route('employees.index')
                ->with('success', 'Employee deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting employee: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting employee. Please try again.');
        }
    }
}
