@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <style>
        /* Responsive Table & Sticky Actions */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .table-responsive::-webkit-scrollbar { height: 7px; }
        .table-responsive::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .table-responsive::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

        .table-concerns {
            width: 100%;
            min-width: 920px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-concerns thead th.col-actions,
        .table-concerns tbody td.col-actions {
            white-space: nowrap;
            text-align: right;
            padding-right: 1.25rem !important;
        }

        /* Mobile Status Pills Scroll & Card Layout */
        @media (max-width: 767.98px) {
            .concerns-nav-pills {
                flex-wrap: nowrap !important;
                overflow-x: auto;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
            }
            .concerns-nav-pills .nav-link {
                white-space: nowrap;
                padding: 7px 15px;
                font-size: 13px;
                flex-shrink: 0;
            }
            .mobile-concern-card {
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                background: #ffffff;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
                margin-bottom: 12px;
                padding: 14px;
            }
        }
    </style>

    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> &rsaquo; <span class="text-dark">Community Reports</span></div>
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
            <h2 class="header-title mb-0">Community Reports</h2>
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 rounded-pill small fw-semibold">
                Reports Management
            </span>
        </div>
        <p class="header-subtitle mb-0">Review, update, and respond to resident-submitted community reports and concerns.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-4 mb-4 shadow-sm border-0" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Status Filters (Scrollable on mobile) --}}
    <ul class="nav nav-pills mb-4 gap-2 flex-wrap concerns-nav-pills">
        @foreach($statusCounts as $status => $count)
            <li class="nav-item">
                <a class="nav-link rounded-pill px-3.5 py-2 {{ $currentStatus === $status ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}"
                   href="{{ request()->fullUrlWithQuery(['status' => $status === 'All' ? null : $status, 'search' => request('search')]) }}">
                    {{ $status }}
                    <span class="badge rounded-pill ms-1 {{ $currentStatus === $status ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-dark' }}">{{ $count }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header border-0 bg-white py-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2 rounded-top-4">
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">
                    <i class="fa-solid fa-shield-halved text-primary me-2"></i>{{ $currentStatus }} Reports
                </h5>
                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11.5px;">
                    {{ count($concerns) }} records
                </span>
            </div>

            <form action="{{ route('admin.concerns') }}" method="GET" class="m-0 w-100 w-sm-auto flex-grow-1 flex-sm-grow-0">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group input-group-sm rounded w-100" style="max-width: 320px; background-color: #f6f9fc;">
                    <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Search by ID, name, title, category..." autocomplete="off">
                    @if(request('search'))
                        <a href="{{ route('admin.concerns', ['status' => request('status')]) }}" class="btn btn-sm btn-transparent border-0 text-muted">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Desktop Table View (Screens >= 768px) --}}
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover align-middle mb-0 table-concerns">
                <thead class="text-muted small" style="background-color: #f8f9fe;">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-nowrap" style="width: 130px;">CONCERN ID</th>
                        <th class="py-3 border-0" style="min-width: 150px;">RESIDENT</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 120px;">CONTACT</th>
                        <th class="py-3 border-0" style="min-width: 170px;">TITLE</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 110px;">CATEGORY</th>
                        <th class="py-3 border-0" style="min-width: 130px;">LOCATION</th>
                        <th class="py-3 border-0 text-nowrap" style="width: 105px;">SUBMITTED</th>
                        <th class="py-3 border-0 text-center text-nowrap" style="width: 115px;">STATUS</th>
                        <th class="pe-4 py-3 border-0 text-end col-actions">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($concerns as $concern)
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
                            <td class="ps-4 fw-bold text-dark text-nowrap">
                                <span class="font-monospace" style="font-size: 13px;">{{ $concern->concern_id }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2 fw-bold text-uppercase flex-shrink-0" style="width: 32px; height: 32px; font-size: 12px;">
                                        {{ substr($concern->resident_name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0" style="max-width: 160px;">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size: 13.5px;">{{ $concern->resident_name }}</div>
                                        <div class="text-muted text-truncate" style="font-size: 11.5px;">{{ $concern->resident_email ?: 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted text-nowrap" style="font-size: 12.5px;">{{ $concern->contact_number ?: 'N/A' }}</td>
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 220px; font-size: 13px;" title="{{ $concern->title }}">
                                    {{ $concern->title }}
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 11px;">{{ $concern->category }}</span>
                            </td>
                            <td class="text-muted" style="font-size: 12.5px;">
                                <div class="text-truncate" style="max-width: 150px;" title="{{ $concern->location }}">
                                    {{ $concern->location }}
                                </div>
                            </td>
                            <td class="text-muted text-nowrap" style="font-size: 12.5px;">{{ $concern->created_at->format('M d, Y') }}</td>
                            <td class="text-center text-nowrap">
                                <span class="{{ $statusClass }}" style="min-width: 90px; font-size: 11.5px; padding: 4px 8px;">{{ $concern->status }}</span>
                            </td>
                            <td class="pe-4 text-end col-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-3 shadow-sm px-3 fw-semibold"
                                        data-bs-toggle="modal"
                                        data-bs-target="#manageConcernModal{{ $concern->id }}"
                                        title="View Details & Update">
                                    <i class="fa-solid fa-eye me-1"></i> View / Update
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i>
                                <h5 class="fw-bold text-dark">No Concerns Found</h5>
                                <div>There are currently no community concerns in this view.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards View (Screens < 768px, matching Request tab) --}}
        <div class="d-md-none p-3" id="concerns-mobile-list">
            @forelse($concerns as $concern)
                @php
                    $statusClass = match($concern->status) {
                        'Resolved'     => 'status-badge status-resolved',
                        'Rejected'     => 'status-badge status-rejected',
                        'In Progress'  => 'status-badge status-in-progress',
                        'Under Review' => 'status-badge status-under-review',
                        default        => 'status-badge status-pending',
                    };
                @endphp
                <div class="mobile-concern-card">
                    {{-- Header: Concern ID + Category + Status --}}
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-1.5 min-w-0">
                            <span class="font-monospace fw-bold text-dark" style="font-size: 13.5px;">{{ $concern->concern_id }}</span>
                            <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 10.5px;">{{ $concern->category }}</span>
                        </div>
                        <span class="{{ $statusClass }}" style="font-size: 11px; padding: 3px 8px;">{{ $concern->status }}</span>
                    </div>

                    {{-- Resident Information --}}
                    <div class="d-flex align-items-center my-2">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-2.5 fw-bold text-uppercase flex-shrink-0" style="width: 38px; height: 38px; font-size: 13px;">
                            {{ substr($concern->resident_name, 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 14.5px;">{{ $concern->resident_name }}</div>
                            <div class="text-muted text-truncate" style="font-size: 12px;">
                                {{ $concern->contact_number ?: $concern->resident_email ?: 'No contact' }}
                            </div>
                        </div>
                    </div>

                    {{-- Concern Title & Details --}}
                    <div class="bg-light rounded-3 p-2.5 my-2 border">
                        <div class="fw-semibold text-dark mb-1" style="font-size: 13.5px;">{{ $concern->title }}</div>
                        <div class="d-flex flex-wrap gap-2 text-muted" style="font-size: 11.5px;">
                            @if($concern->location)
                                <span><i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $concern->location }}</span>
                            @endif
                            <span><i class="fa-regular fa-calendar text-primary me-1"></i>{{ $concern->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2 border-top mt-2">
                        <button type="button" class="btn btn-primary btn-sm w-100 rounded-3 shadow-sm py-2 fw-semibold"
                                data-bs-toggle="modal"
                                data-bs-target="#manageConcernModal{{ $concern->id }}">
                            <i class="fa-solid fa-eye me-1"></i> View & Update Concern
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fa-solid fa-inbox fa-3x mb-2 text-light d-block"></i>
                    <h6 class="fw-bold text-dark">No Concerns Found</h6>
                    <div class="small">There are currently no community concerns in this view.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

@foreach($concerns as $concern)
    <div class="modal fade" id="manageConcernModal{{ $concern->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header bg-light border-0">
                    <div>
                        <h5 class="modal-title fw-bold">Concern Details</h5>
                        <div class="text-muted small">{{ $concern->concern_id }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Resident Name</div>
                            <div class="fw-bold">{{ $concern->resident_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Contact Number</div>
                            <div class="fw-bold">{{ $concern->contact_number ?: 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Email</div>
                            <div class="fw-bold">{{ $concern->resident_email ?: 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Address</div>
                            <div class="fw-bold">{{ $concern->address ?: 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Concern Title</div>
                        <div class="fw-bold">{{ $concern->title }}</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="text-muted small">Category</div>
                            <div class="fw-semibold">{{ $concern->category }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Location</div>
                            <div class="fw-semibold">{{ $concern->location }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Submitted Date</div>
                            <div class="fw-semibold">{{ $concern->created_at->format('M d, Y h:i A') }}</div>
                        </div>
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

                    @if($concern->photo_path)
                        <div class="mb-4">
                            <div class="text-muted small mb-2">Uploaded Photo</div>
                            <a href="{{ asset('storage/' . $concern->photo_path) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/' . $concern->photo_path) }}" alt="Concern photo" class="img-fluid rounded-3 border" style="max-height: 240px; object-fit: cover;">
                            </a>
                        </div>
                    @endif

                    <hr>

                    <form action="{{ route('admin.concerns.update', $concern->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="status{{ $concern->id }}" class="form-label fw-semibold">Update Status</label>
                            <select name="status" id="status{{ $concern->id }}" class="form-select rounded-3" required>
                                @foreach(\App\Models\CommunityConcern::statuses() as $status)
                                    <option value="{{ $status }}" {{ $concern->status === $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_remarks{{ $concern->id }}" class="form-label fw-semibold">Admin Remarks</label>
                            <textarea name="admin_remarks" id="admin_remarks{{ $concern->id }}" rows="4" class="form-control rounded-3" placeholder="Barangay personnel will inspect the area tomorrow.">{{ old('admin_remarks', $concern->admin_remarks) }}</textarea>
                            <div class="form-text">Residents will see the latest remark. Previous remarks are kept in history.</div>
                        </div>

                        @if($concern->remarks->isNotEmpty())
                            <div class="mb-3">
                                <div class="text-muted small mb-2">Remark History</div>
                                <div class="bg-light rounded-3 p-3 border" style="max-height: 160px; overflow-y: auto;">
                                    @foreach($concern->remarks as $remark)
                                        <div class="mb-2 pb-2 border-bottom">
                                            <div style="font-size: 13px;">{{ $remark->remark }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                {{ $remark->admin?->name ?? 'Admin' }} • {{ $remark->created_at->format('M d, Y h:i A') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($concern->updated_by)
                            <div class="text-muted small mb-3">
                                Last updated by {{ $concern->updatedByAdmin?->name ?? 'Admin' }} on {{ $concern->updated_at->format('M d, Y h:i A') }}
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light border rounded-3 px-4" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="fa-solid fa-save me-1"></i> Save Updates
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
