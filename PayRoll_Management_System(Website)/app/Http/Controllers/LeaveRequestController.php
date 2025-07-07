<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = LeaveRequest::with('employee')
                ->when($request->status, function($q) use ($request) {
                    return $q->where('status', $request->status);
                })
                ->when($request->type, function($q) use ($request) {
                    return $q->where('type', $request->type);
                })
                ->when($request->employee_id, function($q) use ($request) {
                    return $q->where('employee_id', $request->employee_id);
                })
                ->when($request->date_from, function($q) use ($request) {
                    return $q->whereDate('start_date', '>=', $request->date_from);
                })
                ->when($request->date_to, function($q) use ($request) {
                    return $q->whereDate('end_date', '<=', $request->date_to);
                });

            $leaveRequests = $query->latest()->paginate(10);
            $employees = Employee::orderBy('first_name')->get();

            return view('leave-requests.index', compact('leaveRequests', 'employees'));
        } catch (\Exception $e) {
            Log::error('Error fetching leave requests: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error fetching leave requests. Please try again.');
        }
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
            return view('leave-requests.create', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Error loading create leave request form: ' . $e->getMessage());
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
                'type' => 'required|in:annual,sick,personal,maternity,paternity,unpaid',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string',
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048'
            ]);

            DB::beginTransaction();

            // Calculate total days
            $totalDays = (new LeaveRequest())->fill([
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date']
            ])->calculateTotalDays();

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            }

            LeaveRequest::create([
                'employee_id' => $validated['employee_id'],
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'reason' => $validated['reason'],
                'total_days' => $totalDays,
                'attachment_path' => $attachmentPath
            ]);

            DB::commit();

            return redirect()->route('leave-requests.index')
                ->with('success', 'Leave request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating leave request: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error submitting leave request. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveRequest $leaveRequest)
    {
        try {
            $leaveRequest->load(['employee', 'approver']);
            return view('leave-requests.show', compact('leaveRequest'));
        } catch (\Exception $e) {
            Log::error('Error showing leave request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error loading leave request details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        try {
            if (!$leaveRequest->isPending()) {
                return redirect()->route('leave-requests.show', $leaveRequest)
                    ->with('error', 'Cannot edit a leave request that has been ' . $leaveRequest->status);
            }

            $employees = Employee::orderBy('first_name')->get();
            return view('leave-requests.edit', compact('leaveRequest', 'employees'));
        } catch (\Exception $e) {
            Log::error('Error loading edit leave request form: ' . $e->getMessage());
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
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        try {
            if (!$leaveRequest->isPending()) {
                return redirect()->route('leave-requests.show', $leaveRequest)
                    ->with('error', 'Cannot update a leave request that has been ' . $leaveRequest->status);
            }

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'type' => 'required|in:annual,sick,personal,maternity,paternity,unpaid',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'reason' => 'required|string',
                'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048'
            ]);

            DB::beginTransaction();

            // Calculate total days
            $totalDays = $leaveRequest->fill([
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date']
            ])->calculateTotalDays();

            // Handle file upload
            if ($request->hasFile('attachment')) {
                // Delete old attachment if exists
                if ($leaveRequest->attachment_path) {
                    Storage::disk('public')->delete($leaveRequest->attachment_path);
                }
                $validated['attachment_path'] = $request->file('attachment')->store('leave-attachments', 'public');
            }

            $leaveRequest->update([
                ...$validated,
                'total_days' => $totalDays
            ]);

            DB::commit();

            return redirect()->route('leave-requests.show', $leaveRequest)
                ->with('success', 'Leave request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating leave request: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating leave request. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        try {
            if (!$leaveRequest->isPending()) {
                return redirect()->route('leave-requests.show', $leaveRequest)
                    ->with('error', 'Cannot delete a leave request that has been ' . $leaveRequest->status);
            }

            DB::beginTransaction();

            // Delete attachment if exists
            if ($leaveRequest->attachment_path) {
                Storage::disk('public')->delete($leaveRequest->attachment_path);
            }

            $leaveRequest->delete();

            DB::commit();

            return redirect()->route('leave-requests.index')
                ->with('success', 'Leave request deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting leave request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting leave request. Please try again.');
        }
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        try {
            if (!$leaveRequest->isPending()) {
                return redirect()->route('leave-requests.show', $leaveRequest)
                    ->with('error', 'This leave request has already been ' . $leaveRequest->status);
            }

            DB::beginTransaction();
            
            $leaveRequest->approve(auth()->id());
            
            DB::commit();

            return redirect()->route('leave-requests.show', $leaveRequest)
                ->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving leave request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error approving leave request. Please try again.');
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        try {
            $validated = $request->validate([
                'rejection_reason' => 'required|string'
            ]);

            if (!$leaveRequest->isPending()) {
                return redirect()->route('leave-requests.show', $leaveRequest)
                    ->with('error', 'This leave request has already been ' . $leaveRequest->status);
            }

            DB::beginTransaction();
            
            $leaveRequest->reject($validated['rejection_reason']);
            
            DB::commit();

            return redirect()->route('leave-requests.show', $leaveRequest)
                ->with('success', 'Leave request rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting leave request: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error rejecting leave request. Please try again.');
        }
    }
}
