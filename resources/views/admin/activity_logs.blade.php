@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> &rsaquo; Activity Logs</div>
        <h2 class="header-title">Activity Logs</h2>
        <p class="header-subtitle mb-0">A chronological record of all admin and system actions.</p>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('admin.activity-logs') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-semibold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="User name, description…" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold">Action Type</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($actionTypes as $type)
                            <option value="{{ $type }}" {{ request('action') === $type ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i></button>
                    <a href="{{ route('admin.activity-logs') }}" class="btn btn-light border w-100" title="Clear Filters"><i class="fa-solid fa-xmark"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Log Entries
            </h6>
            <span class="text-muted small">{{ $logs->total() }} total records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px;">#</th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px;">USER</th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px;">ACTION</th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px;">DESCRIPTION</th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px;">IP ADDRESS</th>
                            <th class="py-3 text-muted small fw-bold border-0 pe-4" style="letter-spacing:0.5px;">DATE & TIME</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 text-muted small border-bottom py-3">{{ $log->id }}</td>
                            <td class="border-bottom py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold flex-shrink-0" style="width:34px;height:34px;font-size:13px;">
                                        {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY' }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size:14px;">{{ $log->user->name ?? 'System' }}</div>
                                        <div class="text-muted" style="font-size:12px;">{{ $log->user->role ?? '–' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="border-bottom py-3">
                                @php
                                    $actionColors = [
                                        'request_accepted'   => 'success',
                                        'request_rejected'   => 'danger',
                                        'request_completed'  => 'success',
                                        'request_ready'      => 'info',
                                        'request_deleted'    => 'danger',
                                        'request_submitted'  => 'primary',
                                        'account_approved'   => 'success',
                                        'account_rejected'   => 'danger',
                                        'document_generated' => 'primary',
                                        'user_updated'       => 'warning',
                                        'user_deactivated'   => 'danger',
                                        'user_reactivated'   => 'success',
                                        'profile_updated'    => 'secondary',
                                        'password_changed'   => 'warning',
                                    ];
                                    $color = $actionColors[$log->action] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-1 rounded-pill fw-semibold" style="font-size:12px;">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="border-bottom py-3 text-dark" style="font-size:14px;max-width:350px;">
                                {{ $log->description }}
                            </td>
                            <td class="border-bottom py-3 text-muted small font-monospace">
                                {{ $log->ip_address ?? '–' }}
                            </td>
                            <td class="border-bottom py-3 pe-4 text-muted small">
                                <div>{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-muted">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 text-light d-block"></i>
                                <h6 class="fw-bold text-dark">No Activity Logs Found</h6>
                                <p class="small mb-0">Actions performed in the admin panel will appear here.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white border-top px-4 py-3 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries
            </div>
            {{-- THE FIX: Changed to pagination::bootstrap-5 --}}
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>
@endsection