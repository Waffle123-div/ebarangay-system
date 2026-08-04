@extends('resident.layouts.resident')

@section('content')
<div class="container py-4">
    
    <div class="mb-4">
        @if($isNewUser)
            <h2 class="header-title">Welcome, {{ $firstName }}!</h2>
        @else
            <h2 class="header-title">Welcome back, {{ $firstName }}!</h2>
        @endif
        <p class="header-subtitle">Manage your document requests and track their progress</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <a href="{{ route('resident.request.create') }}" class="text-decoration-none">
                <div class="card card-custom card-hover p-4">
                    <div class="bg-primary rounded-3 d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-file-circle-plus text-white"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Request Document</h6>
                    <p class="text-muted small mb-0">Submit a new document request</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('resident.request.track') }}" class="text-decoration-none">
                <div class="card card-custom card-hover p-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Track Request</h6>
                    <p class="text-muted small mb-0">Check your request status</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('resident.request.track') }}" class="text-decoration-none">
                <div class="card card-custom card-hover p-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex justify-content-center align-items-center mb-3 shadow-sm" style="width: 45px; height: 45px;">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Request History</h6>
                    <p class="text-muted small mb-0">View all your requests</p>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-warning border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold">Pending Requests</div>
                        <h3 class="fw-bold text-warning mb-0">{{ $pendingCount }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-2 rounded-circle text-warning"><i class="fa-regular fa-clock"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-success border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold">Approved Requests</div>
                        <h3 class="fw-bold text-success mb-0">{{ $approvedCount }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-2 rounded-circle text-success"><i class="fa-regular fa-circle-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom p-3 border-start border-secondary border-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold">Total Requests</div>
                        <h3 class="fw-bold text-dark mb-0">{{ $totalCount }}</h3>
                    </div>
                    <div class="bg-light p-2 rounded-circle text-muted"><i class="fa-regular fa-file-lines"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="fw-bold mb-0">Recent Activity</h6>
            <a href="{{ route('resident.activity-logs') }}" class="text-primary text-decoration-none small fw-bold">
                View Full Log <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-borderless align-middle">
                <tbody>
                    @forelse($recentRequests as $request)
                    <tr>
                        <td style="width: 50px;">
                            @if($request->status == 'Approved')
                                <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="fa-solid fa-file-lines"></i></div>
                            @elseif($request->status == 'Pending')
                                <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="fa-solid fa-file-lines"></i></div>
                            @else
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="fa-solid fa-file-lines"></i></div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $request->document_type }}</div>
                            <div class="text-muted" style="font-size: 11px;">Submitted on {{ $request->created_at->format('M d, Y') }} • ID: {{ $request->tracking_number }}</div>
                        </td>
                        <td class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                @if($request->status == 'Approved')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Approved</span>
                                @elseif($request->status == 'Pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Pending</span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">{{ $request->status }}</span>
                                @endif
                                <a href="{{ route('resident.receipt.view', $request->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1" style="font-size: 12px;" title="View Receipt">
                                    <i class="fa-solid fa-receipt me-1"></i> Receipt
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            <i class="fa-regular fa-folder-open mb-2 fa-2x text-light"></i>
                            <div>No recent activity found.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(is_null(auth()->user()->security_pin))
    <div class="modal" id="setupPinModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 bg-primary bg-opacity-10 text-primary py-4 px-4 justify-content-center flex-column">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-shield-halved fs-4"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0">Set Up Your Security PIN</h5>
                    <p class="small text-muted mb-0 mt-2 text-center">Create a 6-digit PIN for faster, secure logins to the E-Barangay system.</p>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('resident.pin.setup') }}" method="POST" id="pinSetupForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Enter 6-Digit PIN</label>
                            <input type="password" name="security_pin" class="form-control form-control-lg text-center fw-bold rounded-3 bg-light border-0" 
                                   maxlength="6" inputmode="numeric" pattern="[0-9]*" 
                                   placeholder="● ● ● ● ● ●" style="letter-spacing: 8px;" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">Confirm 6-Digit PIN</label>
                            <input type="password" name="security_pin_confirmation" class="form-control form-control-lg text-center fw-bold rounded-3 bg-light border-0" 
                                   maxlength="6" inputmode="numeric" pattern="[0-9]*" 
                                   placeholder="● ● ● ● ● ●" style="letter-spacing: 8px;" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm">
                            <i class="fa-solid fa-lock me-2"></i> Save Secure PIN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var pinModal = new bootstrap.Modal(document.getElementById('setupPinModal'));
            pinModal.show();
        });
    </script>
@endif

@endsection