@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> > Community Concerns</div>
        <h2 class="header-title">Community Concerns</h2>
        <p class="header-subtitle mb-0">Review, update, and respond to resident-submitted community concerns</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <ul class="nav nav-pills mb-3 gap-2 flex-wrap">
        @foreach($statusCounts as $status => $count)
            <li class="nav-item">
                <a class="nav-link rounded-pill px-4 {{ $currentStatus === $status ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}"
                   href="{{ request()->fullUrlWithQuery(['status' => $status === 'All' ? null : $status, 'search' => request('search')]) }}">
                    {{ $status }}
                    <span class="badge rounded-pill ms-1 {{ $currentStatus === $status ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $count }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header border-0 bg-white py-4 px-4 d-flex justify-content-end align-items-center rounded-top-4">
            <form action="{{ route('admin.concerns') }}" method="GET" class="m-0">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group input-group-sm rounded" style="width: 320px; background-color: #f6f9fc;">
                    <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Search by ID, name, title, category..." autocomplete="off">
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="text-muted small" style="background-color: #f8f9fe;">
                    <tr>
                        <th class="ps-4 py-3 border-0">CONCERN ID</th>
                        <th class="py-3 border-0">RESIDENT</th>
                        <th class="py-3 border-0">CONTACT</th>
                        <th class="py-3 border-0">TITLE</th>
                        <th class="py-3 border-0">CATEGORY</th>
                        <th class="py-3 border-0">LOCATION</th>
                        <th class="py-3 border-0">SUBMITTED</th>
                        <th class="py-3 border-0">STATUS</th>
                        <th class="pe-4 py-3 border-0 text-end">ACTION</th>
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
                            <td class="ps-4 fw-bold text-dark">{{ $concern->concern_id }}</td>
                            <td>
                                <div class="fw-semibold text-dark" style="font-size: 14px;">{{ $concern->resident_name }}</div>
                                <div class="text-muted" style="font-size: 12px;">{{ $concern->resident_email ?: 'No email' }}</div>
                            </td>
                            <td class="text-muted" style="font-size: 13px;">{{ $concern->contact_number ?: 'N/A' }}</td>
                            <td>{{ Str::limit($concern->title, 35) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $concern->category }}</span></td>
                            <td class="text-muted" style="font-size: 13px;">{{ Str::limit($concern->location, 30) }}</td>
                            <td class="text-muted" style="font-size: 13px;">{{ $concern->created_at->format('M d, Y') }}</td>
                            <td><span class="{{ $statusClass }}">{{ $concern->status }}</span></td>
                            <td class="pe-4 text-end">
                                <button type="button" class="btn btn-primary btn-sm rounded-3 shadow-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#manageConcernModal{{ $concern->id }}">
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
