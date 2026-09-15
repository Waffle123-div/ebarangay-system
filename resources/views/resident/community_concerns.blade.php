@extends('resident.layouts.resident')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('resident.dashboard') }}" class="text-muted text-decoration-none small fw-bold mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>
        <h3 class="fw-bold text-dark mb-1">My Community Concerns</h3>
        <p class="text-muted small mb-0">Track the progress of your submitted concerns and read barangay feedback.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-4">
        <button type="button" class="btn btn-primary rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#submitConcernModal">
            <i class="fa-solid fa-plus me-1"></i> Submit New Concern
        </button>
    </div>

    @if($concerns->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <div class="text-muted mb-3"><i class="fa-solid fa-bullhorn fs-1"></i></div>
            <h5 class="fw-bold text-dark">No Concerns Submitted Yet</h5>
            <p class="text-muted small mb-0">You haven't submitted any community concerns yet. Click below to report an issue in your community.</p>
            <div class="mt-4">
                <button type="button" class="btn btn-primary rounded-3 px-4" data-bs-toggle="modal" data-bs-target="#submitConcernModal">
                    <i class="fa-solid fa-plus me-1"></i> Submit a Concern
                </button>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted small" style="background-color: #f8f9fe;">
                        <tr>
                            <th class="ps-4 py-3 border-0">CONCERN ID</th>
                            <th class="py-3 border-0">TITLE</th>
                            <th class="py-3 border-0">CATEGORY</th>
                            <th class="py-3 border-0">SUBMITTED</th>
                            <th class="py-3 border-0">STATUS</th>
                            <th class="py-3 border-0">LAST UPDATED</th>
                            <th class="pe-4 py-3 border-0 text-end">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($concerns as $concern)
                            @php
                                $statusClass = match($concern->status) {
                                    'Resolved'     => 'status-badge status-resolved',
                                    'Rejected'     => 'status-badge status-rejected',
                                    'In Progress'  => 'status-badge status-in-progress',
                                    'Under Review' => 'status-badge status-under-review',
                                    default        => 'status-badge status-pending',
                                };
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $concern->concern_id }}</td>
                                <td>
                                    <div class="fw-semibold text-dark" style="font-size: 14px;">{{ $concern->title }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ Str::limit($concern->location, 40) }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $concern->category }}</span></td>
                                <td class="text-muted" style="font-size: 13px;">{{ $concern->created_at->format('M d, Y') }}</td>
                                <td><span class="{{ $statusClass }}">{{ $concern->status }}</span></td>
                                <td class="text-muted" style="font-size: 13px;">{{ $concern->updated_at->format('M d, Y h:i A') }}</td>
                                <td class="pe-4 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#concernModal{{ $concern->id }}">
                                        <i class="fa-solid fa-eye me-1"></i> View Details
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@foreach($concerns as $concern)
    @php
        $statusClass = match($concern->status) {
            'Resolved'     => 'status-badge status-resolved',
            'Rejected'     => 'status-badge status-rejected',
            'In Progress'  => 'status-badge status-in-progress',
            'Under Review' => 'status-badge status-under-review',
            default        => 'status-badge status-pending',
        };
    @endphp
    <div class="modal fade" id="concernModal{{ $concern->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">{{ $concern->title }}</h5>
                        <div class="text-muted small">{{ $concern->concern_id }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Category</div>
                            <div class="fw-semibold">{{ $concern->category }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Status</div>
                            <span class="{{ $statusClass }}">{{ $concern->status }}</span>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Submitted Date</div>
                            <div class="fw-semibold">{{ $concern->created_at->format('F d, Y') }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Location</div>
                        <div class="bg-light rounded-3 p-3 border">{{ $concern->location }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Description</div>
                        <div class="bg-light rounded-3 p-3 border" style="font-size: 14px;">{{ $concern->description }}</div>
                    </div>

                    @if($concern->incident_date)
                        <div class="mb-3">
                            <div class="text-muted small">Date of Incident</div>
                            <div class="fw-semibold">{{ $concern->incident_date->format('F d, Y') }}</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Admin Remarks</div>
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3 border border-primary border-opacity-25" style="font-size: 14px;">
                            @if($concern->admin_remarks)
                                {{ $concern->admin_remarks }}
                            @else
                                <span class="text-muted">No remarks from barangay staff yet.</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Last Updated</div>
                        <div class="fw-semibold">{{ $concern->updated_at->format('F d, Y - h:i A') }}</div>
                    </div>

                    @if($concern->photo_path)
                        <div>
                            <div class="text-muted small mb-2">Attached Photo</div>
                            <a href="{{ asset('storage/' . $concern->photo_path) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/' . $concern->photo_path) }}" alt="Concern attachment" class="img-fluid rounded-3 border" style="max-height: 280px; object-fit: cover;">
                            </a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Submit New Concern Modal --}}
<div class="modal fade" id="submitConcernModal" tabindex="-1" aria-labelledby="submitConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="submitConcernModalLabel">Submit New Concern</h5>
                    <p class="text-muted small mb-0">Report a community issue directly from your resident portal.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4">
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @include('partials.community-concern-form', [
                    'formId' => 'submitConcernForm',
                    'context' => 'modal',
                    'isResident' => true,
                    'guestLocked' => false,
                    'authUser' => $authUser,
                    'categories' => $categories,
                    'residentAddress' => $residentAddress,
                    'showResidentInfo' => false,
                    'idPrefix' => 'modal',
                ])
            </div>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('submitConcernModal');
        if (modalEl) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    });
</script>
@endif
@endsection
