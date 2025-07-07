@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fs-2 m-0">Attendance Records</h2>
        <a href="{{ route('attendance.create') }}" class="btn btn-primary">Record Attendance</a>
    </div>

    <div class="card">
        <div class="card-header">
            <form action="{{ route('attendance.index') }}" method="GET" class="d-flex">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search attendance..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                @if(request('search'))
                    <a href="{{ route('attendance.index') }}" class="btn btn-link ms-2">Clear</a>
                @endif
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->date->format('Y-m-d') }}</td>
                                <td>
                                    <div>{{ $attendance->employee->name }}</div>
                                    <div class="text-muted small">{{ $attendance->employee->employee_id }}</div>
                                </td>
                                <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i:s') : '-' }}</td>
                                <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i:s') : '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($attendance->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attendance record?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No attendance records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .table-responsive::-webkit-scrollbar {
        width: 8px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8f9fa;
    }
</style>
@endsection 