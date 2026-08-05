@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Admin Dashboard</h3>
        <p class="text-muted small">Manage document requests and system operations</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Total Requests</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalRequests ?? 0 }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-file-lines fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Pending Reviews</div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $pendingReviews ?? 0 }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-clock fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Approved Today</div>
                        <h3 class="fw-bold mb-0 text-success">{{ $approvedToday ?? 0 }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Active Users</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $activeUsers ?? 0 }}</h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-user fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">Request Management</h5>
            <div class="d-flex gap-2">
                <div class="position-relative">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 13px;"></i>
                    <input type="text" class="form-control form-control-sm bg-light border-0 ps-5 py-2 rounded-2" placeholder="Search requests..." style="width: 250px;">
                </div>
                <button class="btn btn-light btn-sm border bg-white d-flex align-items-center gap-2 px-3 rounded-2 text-muted fw-semibold">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted small fw-bold py-3 ps-4 border-0" style="letter-spacing: 0.5px;">REQUEST ID</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">RESIDENT</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">DOCUMENT TYPE</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">DATE SUBMITTED</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">STATUS</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($recentRequests as $request)
                        <tr>
                            <td class="ps-4 fw-bold text-dark border-bottom py-3">{{ $request->tracking_number }}</td>
                            <td class="text-muted border-bottom py-3">{{ $request->resident_name }}</td>
                            <td class="text-muted border-bottom py-3">{{ $request->document_type }}</td>
                            <td class="text-muted border-bottom py-3">{{ $request->created_at->format('M d, Y') }}</td>
                            
                            <td class="border-bottom py-3">
                                @if($request->status == 'Approved')
                                    <span class="status-badge status-approved">Approved</span>
                                @elseif($request->status == 'Pending')
                                    <span class="status-badge status-pending">Pending</span>
                                @elseif($request->status == 'Under Review')
                                    <span class="status-badge status-under-review">Under Review</span>
                                @elseif($request->status == 'Cancelled' || $request->status == 'Rejected')
                                    <span class="status-badge status-rejected">Rejected</span>
                                @else
                                    <span class="status-badge status-inactive">{{ $request->status }}</span>
                                @endif
                            </td>
                            
                            <td class="border-bottom py-3">
                                <div class="d-flex align-items-center gap-3" style="font-size: 14px;">
                                    
                                    <a href="{{ route('admin.requests') }}" class="text-primary text-decoration-none fw-bold">View</a>

                                    <form action="{{ route('admin.requests.accept', $request->id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-link p-0 text-success text-decoration-none fw-bold shadow-none" style="font-size: 14px;">Approve</button>
                                    </form>

                                    <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to reject this document request?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-link p-0 text-danger text-decoration-none fw-bold shadow-none" style="font-size: 14px;">Reject</button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No recent requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 mb-3 text-muted small">
        &copy; 2026 E-Barangay System
    </div>

</div>
@endsection