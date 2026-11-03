@extends('resident.layouts.resident')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Activity Logs</h3>
        <p class="text-muted small mb-0">Track and review your personal activity history on E-Barangay</p>
    </div>

    {{-- Filter & Search Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('resident.activity-logs') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold">Search Activity</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search keywords..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
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
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    @if(request('search') || request('action'))
                        <a href="{{ route('resident.activity-logs') }}" class="btn btn-light border w-100 rounded-3" title="Clear Filters">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Activity Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Log Entries
            </h6>
            <span class="text-muted small">{{ $logs->total() }} recorded actions</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-muted small fw-bold border-0">ACTION</th>
                            <th class="py-3 text-muted small fw-bold border-0">DESCRIPTION</th>
                            <th class="py-3 text-muted small fw-bold border-0 text-center">VIEW</th>
                            <th class="py-3 text-muted small fw-bold border-0 pe-4 text-end">DATE & TIME</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($logs as $log)
                        <tr>
                            <td class="ps-4 border-bottom py-3">
                                @php
                                    $badgeClass = 'bg-secondary text-secondary';
                                    if (str_contains($log->action, 'submit') || str_contains($log->action, 'create')) {
                                        $badgeClass = 'bg-primary text-primary';
                                    } elseif (str_contains($log->action, 'update') || str_contains($log->action, 'password') || str_contains($log->action, 'pin')) {
                                        $badgeClass = 'bg-warning text-warning';
                                    } elseif (str_contains($log->action, 'receipt') || str_contains($log->action, 'view')) {
                                        $badgeClass = 'bg-info text-info';
                                    } elseif (str_contains($log->action, 'approved') || str_contains($log->action, 'completed')) {
                                        $badgeClass = 'bg-success text-success';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }} bg-opacity-10 px-3 py-2 rounded-pill fw-bold" style="font-size: 11px;">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="border-bottom py-3 text-dark fw-medium" style="font-size: 14px;">
                                {{ $log->description }}
                            </td>
                            <td class="border-bottom py-3 text-center">
                                @php
                                    preg_match('/#(REQ-[\w\-]+)/', $log->description, $matches);
                                    $trackingNum = $matches[1] ?? null;
                                    $docRequest = $trackingNum ? \App\Models\DocumentRequest::where('tracking_number', $trackingNum)->first() : null;
                                @endphp
                                @if($docRequest)
                                    <a href="{{ route('resident.receipt.view', $docRequest->id) }}" class="btn btn-sm btn-outline-primary rounded-3 px-3" title="View Receipt">
                                        <i class="fa-solid fa-eye me-1"></i> View
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="border-bottom py-3 pe-4 text-muted small text-end">
                                <div class="fw-semibold text-dark">{{ $log->created_at->format('M d, Y') }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $log->created_at->format('h:i A') }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 text-light d-block"></i>
                                <h6 class="fw-bold text-dark">No Activity Logs Found</h6>
                                <p class="small mb-0">Your account actions (such as submitting document requests and updating settings) will appear here.</p>
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
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
