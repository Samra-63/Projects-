@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Edit Attendance Record</h3>
                    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('attendance.update', $attendance) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label">Employee</label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" 
                                    id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" 
                                            {{ old('employee_id', $attendance->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->full_name }} ({{ $employee->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                   id="date" name="date" 
                                   value="{{ old('date', $attendance->date->format('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="present" {{ old('status', $attendance->status) == 'present' ? 'selected' : '' }}>Present</option>
                                <option value="absent" {{ old('status', $attendance->status) == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="late" {{ old('status', $attendance->status) == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="half_day" {{ old('status', $attendance->status) == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                <option value="on_leave" {{ old('status', $attendance->status) == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="clock_in" class="form-label">Clock In</label>
                            <input type="time" class="form-control @error('clock_in') is-invalid @enderror" 
                                   id="clock_in" name="clock_in" 
                                   value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                        </div>

                        <div class="col-md-3">
                            <label for="clock_out" class="form-label">Clock Out</label>
                            <input type="time" class="form-control @error('clock_out') is-invalid @enderror" 
                                   id="clock_out" name="clock_out" 
                                   value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Attendance
                            </button>
                            <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const clockInInput = document.getElementById('clock_in');
    const clockOutInput = document.getElementById('clock_out');

    function toggleTimeInputs() {
        const isAbsentOrLeave = ['absent', 'on_leave'].includes(statusSelect.value);
        clockInInput.disabled = isAbsentOrLeave;
        clockOutInput.disabled = isAbsentOrLeave;
        
        if (isAbsentOrLeave) {
            clockInInput.value = '';
            clockOutInput.value = '';
        }
    }

    statusSelect.addEventListener('change', toggleTimeInputs);
    toggleTimeInputs(); // Run on initial load
});
</script>
@endpush
@endsection 