@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <div class="text-muted small mb-1"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a> > Requests</div>
        <h2 class="header-title">Request Management</h2>
        <p class="header-subtitle mb-0">Manage and process document requests from residents</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Total Requests</div>
                        <h3 class="fw-bold mb-0 text-dark" id="kpi-total">{{ $totalRequests }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">All-time records</div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Pending Reviews</div>
                        <h3 class="fw-bold mb-0 text-warning" id="kpi-pending">{{ $pendingReviews }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Requires attention</div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Approved Today</div>
                        <h3 class="fw-bold mb-0 text-success" id="kpi-approved">{{ $approvedToday }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Processing complete</div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Overdue Requests</div>
                        <h3 class="fw-bold mb-0 text-danger" id="kpi-overdue">{{ $overdueRequests }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Past deadline</div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small fw-bold mb-1">Avg Processing</div>
                        <h3 class="fw-bold mb-0 text-dark" id="kpi-avg">{{ $avgProcessing }}</h3>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 12px;">Days per request</div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills mb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'Pending_Review' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Pending_Review']) }}">
                <i class="fa-solid fa-inbox me-1"></i> Pending & Review 
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'Pending_Review' ? 'bg-white text-primary' : 'bg-secondary' }}" id="tab-badge-pending">{{ $tabCounts['Pending_Review'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'Processing' ? 'active fw-bold shadow-sm' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Processing']) }}">
                <i class="fa-solid fa-gear me-1"></i> Processing
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'Processing' ? 'bg-white text-primary' : 'bg-secondary' }}" id="tab-badge-processing">{{ $tabCounts['Processing'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'Ready' ? 'active fw-bold shadow-sm bg-info border-info text-white' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Ready']) }}">
                <i class="fa-solid fa-file-signature me-1"></i> Ready for Pick-up
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'Ready' ? 'bg-white text-info' : 'bg-secondary' }}" id="tab-badge-ready">{{ $tabCounts['Ready'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'Completed' ? 'active fw-bold shadow-sm bg-success border-success text-white' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Completed']) }}">
                <i class="fa-solid fa-check-double me-1"></i> Completed
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'Completed' ? 'bg-white text-success' : 'bg-secondary' }}" id="tab-badge-completed">{{ $tabCounts['Completed'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill px-4 {{ $currentStatus == 'Rejected' ? 'active fw-bold shadow-sm bg-danger border-danger text-white' : 'bg-white text-muted border' }}" 
               href="{{ request()->fullUrlWithQuery(['status' => 'Rejected']) }}">
                <i class="fa-solid fa-ban me-1"></i> Rejected
                <span class="badge rounded-pill ms-1 {{ $currentStatus == 'Rejected' ? 'bg-white text-danger' : 'bg-secondary' }}" id="tab-badge-rejected">{{ $tabCounts['Rejected'] }}</span>
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header border-0 bg-white py-4 px-4 d-flex justify-content-end align-items-center gap-3 rounded-top-4">
            
            <form action="{{ route('admin.requests') }}" method="GET" class="m-0" id="searchForm">
                @if(request('document_type'))
                    <input type="hidden" name="document_type" value="{{ request('document_type') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                
                <div class="input-group input-group-sm rounded" style="width: 300px; background-color: #f6f9fc;">
                    <button type="submit" class="input-group-text border-0 bg-transparent text-muted"><i class="fa fa-search"></i></button>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" class="form-control border-0 bg-transparent shadow-none" placeholder="Search by ID, name, or document type..." autocomplete="off">
                </div>
            </form>
            
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border px-3 fw-semibold dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-filter text-muted me-1"></i> 
                        {{ $currentDocType == 'All' ? 'Filters' : $currentDocType }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="filterDropdown" style="font-size: 14px;">
                        <li><a class="dropdown-item {{ $currentDocType == 'All' ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => null]) }}">All Documents</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item {{ $currentDocType == 'Barangay Clearance' ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => 'Barangay Clearance']) }}">Barangay Clearance</a></li>
                        <li><a class="dropdown-item {{ $currentDocType == 'Certificate of Indigency' ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => 'Certificate of Indigency']) }}">Certificate of Indigency</a></li>
                        <li><a class="dropdown-item {{ $currentDocType == 'Certificate of Residency' ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => 'Certificate of Residency']) }}">Certificate of Residency</a></li>
                        <li><a class="dropdown-item {{ $currentDocType == 'Business Permit' ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : '' }}" href="{{ request()->fullUrlWithQuery(['document_type' => 'Business Permit']) }}">Business Permit</a></li>
                    </ul>
                </div>
                <button class="btn btn-sm btn-light border px-3 fw-semibold"><i class="fa-solid fa-download text-muted me-1"></i> Export</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="text-muted small" style="background-color: #f8f9fe;">
                    <tr>
                        <th class="ps-4 py-3 border-0"><input class="form-check-input" type="checkbox"></th>
                        <th class="py-3 border-0">REQUEST ID</th>
                        <th class="py-3 border-0">APPLICANT</th>
                        <th class="py-3 border-0">DOCUMENT TYPE</th>
                        <th class="py-3 border-0">SUBMISSION DATE</th>
                        <th class="py-3 border-0">STATUS</th>
                        <th class="pe-4 py-3 border-0 text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="border-top-0" id="requests-tbody">
                    @forelse($allRequests as $request)
                    
                    @php
                        $isUrgent = ($currentStatus == 'Pending_Review' && $request->status == 'Pending' && $request->created_at->diffInHours(\Carbon\Carbon::now()) > 48);
                    @endphp
                    
                    <tr class="{{ $isUrgent ? 'bg-danger bg-opacity-10' : '' }}">
                        <td class="ps-4"><input class="form-check-input" type="checkbox"></td>
                        <td class="fw-bold text-dark">
                            {{ $request->tracking_number }}
                            @if($isUrgent)
                                <div class="text-danger small"><i class="fa-solid fa-triangle-exclamation"></i> Overdue</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 fw-bold text-uppercase" style="width: 36px; height: 36px; font-size: 13px;">
                                    {{ substr($request->first_name, 0, 1) }}{{ substr($request->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 14px;">{{ $request->first_name }} {{ $request->last_name }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $request->resident_email ?? $request->contact_number ?? 'No contact info provided' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->document_type }}</td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>
                            <div id="status-badge-{{ $request->id }}">
                                @if($request->status == 'Completed')
                                    <span class="status-badge status-completed">Completed</span>
                                @elseif($request->status == 'Ready')
                                    <span class="status-badge status-ready">Ready for Pickup</span>
                                @elseif($request->status == 'Processing')
                                    <span class="status-badge status-processing">Processing</span>
                                @elseif($request->status == 'Under Review')
                                    <span class="status-badge status-under-review">Under Review</span>
                                @elseif($request->status == 'Rejected')
                                    <span class="status-badge status-rejected">Rejected</span>
                                @else
                                    <span class="status-badge status-pending">Pending</span>
                                @endif
                            </div>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-2">
                                
                                <button type="button" class="btn btn-secondary btn-sm rounded-3 shadow-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewModal{{ $request->id }}" 
                                        title="View Details">
                                    <i class="fa-solid fa-eye"></i> View
                                </button>

                                @if($currentStatus == 'Pending_Review')
                                    <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete this request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-3 shadow-sm" title="Delete Request"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                
                                @elseif($currentStatus == 'Processing')
                                    <a href="{{ route('admin.requests.generate', $request->id) }}" 
                                       class="btn btn-primary btn-sm rounded-3 shadow-sm" 
                                       onclick="markAsProcessing({{ $request->id }})" 
                                       title="Generate Document">
                                        <i class="fa-solid fa-file-word"></i> Generate Word Doc
                                    </a>

                                    <div id="ready-btn-container-{{ $request->id }}" class="{{ $request->is_generated ? '' : 'd-none' }}">
                                        <form action="{{ route('admin.requests.ready', $request->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-info text-white btn-sm rounded-3 shadow-sm" title="Mark as Ready">
                                                <i class="fa-solid fa-bell"></i> Mark Ready for Pickup
                                            </button>
                                        </form>
                                    </div>

                                @elseif($currentStatus == 'Ready')
                                    <a href="{{ route('admin.requests.generate', $request->id) }}" class="btn btn-light border btn-sm rounded-3 shadow-sm text-muted" title="Re-download Document">
                                        <i class="fa-solid fa-download"></i> Re-download
                                    </a>
                                    <form action="{{ route('admin.requests.complete', $request->id) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm rounded-3 shadow-sm" title="Mark as Completed">
                                            <i class="fa-solid fa-check-double"></i> Mark Claimed / Completed
                                        </button>
                                    </form>

                                @elseif($currentStatus == 'Rejected')
                                    <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" class="m-0" onsubmit="return confirm('Permanently delete this rejected request?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-3 shadow-sm" title="Delete Permanently">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                                
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-inbox fa-3x mb-3 text-light"></i>
                            <h5 class="fw-bold text-dark">No Requests Found</h5>
                            <div>There are currently no documents in the <b>{{ str_replace('_', ' ', $currentStatus) }}</b> tab.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modals-container">
    @foreach($allRequests as $request)
    <div class="modal" id="viewModal{{ $request->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $request->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="viewModalLabel{{ $request->id }}">
                        <i class="fa-solid fa-file-invoice text-primary me-2"></i>Request Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="row mb-3">
                        <div class="col-6">
                            <span class="text-muted small d-block">Tracking Number</span>
                            <span class="fw-bold text-dark">{{ $request->tracking_number }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Status</span>
                            <span class="fw-bold text-primary" id="modal-status-{{ $request->id }}">{{ $request->status }}</span>
                        </div>
                    </div>
                    <hr class="text-muted">
                    <div class="row mb-3">
                        <div class="col-8">
                            <span class="text-muted small d-block">Applicant Name</span>
                            <span class="fw-bold text-dark">{{ $request->first_name }} {{ $request->middle_initial }} {{ $request->last_name }}</span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted small d-block">Age</span>
                            <span class="fw-bold text-dark">{{ $request->age ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block">Contact Info</span>
                        <span class="fw-bold text-dark">{{ $request->resident_email ?? $request->contact_number ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block">Document Requested</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mt-1">{{ $request->document_type }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block mb-1">Stated Purpose</span>
                        <div class="bg-light p-3 rounded-3 border" style="font-size: 14px;">
                            {{ $request->purpose ?? 'No purpose provided by the resident.' }}
                        </div>
                    </div>
                    <div class="mb-1">
                        <span class="text-muted small d-block">Date Submitted</span>
                        <span class="fw-bold text-dark" style="font-size: 14px;">{{ $request->created_at->format('F d, Y - h:i A') }}</span>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light border px-4 rounded-3" data-bs-dismiss="modal">Close</button>
                    
                    @if($request->status == 'Pending' || $request->status == 'Under Review')
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to reject this request? The resident will be notified.');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-danger px-3 rounded-3">
                                    <i class="fa-solid fa-xmark me-1"></i> Reject
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.requests.accept', $request->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success px-3 rounded-3 shadow-sm">
                                    <i class="fa-solid fa-check me-1"></i> Accept for Processing
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<script>
    function markAsProcessing(id) {
        let badgeContainer = document.getElementById('status-badge-' + id);
        if(badgeContainer) {
            badgeContainer.innerHTML = '<span class="status-badge status-processing">Processing</span>';
        }
        
        let modalStatus = document.getElementById('modal-status-' + id);
        if(modalStatus) {
            modalStatus.innerHTML = 'Processing';
            modalStatus.className = 'fw-bold text-primary';
        }

        let readyBtnContainer = document.getElementById('ready-btn-container-' + id);
        if (readyBtnContainer) {
            readyBtnContainer.classList.remove('d-none');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        let debounceTimer;

        // 1. Live Search Logic
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                const searchTerm = this.value;
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.set('search', searchTerm); 
                
                const fetchUrl = window.location.pathname + '?' + urlParams.toString();
                window.history.pushState({}, '', fetchUrl);

                fetch(fetchUrl)
                    .then(response => response.text())
                    .then(html => {
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, 'text/html');
                        
                        let newTbody = doc.getElementById('requests-tbody');
                        let currentTbody = document.getElementById('requests-tbody');
                        if (newTbody && currentTbody) currentTbody.innerHTML = newTbody.innerHTML;

                        let newModals = doc.getElementById('modals-container');
                        let currentModals = document.getElementById('modals-container');
                        if (newModals && currentModals) currentModals.innerHTML = newModals.innerHTML;
                    })
                    .catch(error => console.error("Live search error:", error));
            }, 300);
        });

        // 2. Real-Time Auto-Refresh Polling (5 Seconds)
        setInterval(function() {
            // Stricter checks to ensure we NEVER refresh while a modal is opening/open
            let isModalOpen = document.body.classList.contains('modal-open') || 
                              document.querySelector('.modal.show') !== null ||
                              document.querySelector('.modal-backdrop') !== null;

            if (document.activeElement === searchInput || isModalOpen) {
                return; 
            }

            const fetchUrl = window.location.href;

            fetch(fetchUrl)
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');

                    let newTbody = doc.getElementById('requests-tbody');
                    let currentTbody = document.getElementById('requests-tbody');
                    if (newTbody && currentTbody) currentTbody.innerHTML = newTbody.innerHTML;

                    let newModals = doc.getElementById('modals-container');
                    let currentModals = document.getElementById('modals-container');
                    if (newModals && currentModals) currentModals.innerHTML = newModals.innerHTML;

                    const elementIdsToUpdate = [
                        'kpi-total', 'kpi-pending', 'kpi-approved', 'kpi-overdue', 'kpi-avg',
                        'tab-badge-pending', 'tab-badge-processing', 'tab-badge-ready', 'tab-badge-completed', 'tab-badge-rejected'
                    ];

                    elementIdsToUpdate.forEach(id => {
                        let newEl = doc.getElementById(id);
                        let oldEl = document.getElementById(id);
                        if (newEl && oldEl) oldEl.innerHTML = newEl.innerHTML;
                    });
                })
                .catch(error => console.error("Auto-refresh error:", error));

        }, 5000); 
    });
</script>
@endsection