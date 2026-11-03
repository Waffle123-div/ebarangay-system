@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Admin Dashboard</h3>
        <p class="text-muted small mb-0">Manage document requests and system operations</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 g-sm-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1 text-truncate">Total Requests</div>
                        <h3 class="fw-bold mb-0 text-dark fs-3 fs-sm-2">{{ $totalRequests ?? 0 }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="fa-solid fa-file-lines fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1 text-truncate">Pending Reviews</div>
                        <h3 class="fw-bold mb-0 text-warning fs-3 fs-sm-2">{{ $pendingReviews ?? 0 }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="fa-solid fa-clock fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1 text-truncate">Approved Today</div>
                        <h3 class="fw-bold mb-0 text-success fs-3 fs-sm-2">{{ $approvedToday ?? 0 }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="fa-solid fa-check fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 p-sm-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold mb-1 text-truncate">Active Users</div>
                        <h3 class="fw-bold mb-0 text-dark fs-3 fs-sm-2">{{ $activeUsers ?? 0 }}</h3>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                        <i class="fa-solid fa-user fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 p-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
            <h5 class="fw-bold mb-0 text-dark">Request Management</h5>
            <div class="d-flex gap-2 w-100 w-sm-auto">
                <div class="position-relative flex-grow-1 flex-sm-grow-0">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 13px;"></i>
                    <input type="text" class="form-control form-control-sm bg-light border-0 ps-5 py-2 rounded-pill" placeholder="Search requests..." style="min-width: 180px;">
                </div>
                <a href="{{ route('admin.requests') }}" class="btn btn-light btn-sm border bg-white d-flex align-items-center gap-2 px-3 rounded-pill text-muted fw-semibold flex-shrink-0">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> All
                </a>
            </div>
        </div>

        {{-- Bulk Selection Bar --}}
        <div id="bulkActionsBar" class="d-none bg-primary bg-opacity-10 border-bottom px-3 px-md-4 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge">0 Selected</span>
                <span class="text-muted small d-none d-sm-inline">Select multiple requests to delete in bulk.</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold bg-white" id="deselectAllBtn">
                    <i class="fa-solid fa-xmark me-1"></i> Deselect
                </button>
                <button type="button" class="btn btn-sm btn-danger rounded-3 px-3 fw-semibold shadow-sm" id="bulkDeleteBtn">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Selected (<span id="bulkDeleteCount">0</span>)
                </button>
            </div>
        </div>

        <div class="card-body p-0">
            {{-- Desktop Table View --}}
            <div class="d-none d-md-block table-responsive">
                <table class="table table-hover align-middle mb-0" id="dashboardRequestsTable" style="min-width: 760px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-muted small fw-bold py-3 ps-4 border-0 text-center" style="width: 44px;">
                                <input class="form-check-input" type="checkbox" id="selectAllDashboardReqs" title="Select All">
                            </th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">REQUEST ID</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">RESIDENT</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">DOCUMENT TYPE</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">DATE SUBMITTED</th>
                            <th class="text-muted small fw-bold py-3 border-0" style="letter-spacing: 0.5px;">STATUS</th>
                            <th class="text-muted small fw-bold py-3 pe-4 border-0 text-end" style="letter-spacing: 0.5px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($recentRequests as $request)
                        <tr id="req-row-{{ $request->id }}">
                            <td class="ps-4 text-center py-3" style="width: 44px;">
                                <input class="form-check-input dashboard-req-checkbox" type="checkbox" value="{{ $request->id }}" data-tracking="{{ $request->tracking_number }}">
                            </td>
                            <td class="fw-bold text-dark border-bottom py-3">{{ $request->tracking_number }}</td>
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
                            
                            <td class="border-bottom py-3 pe-4 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-3" style="font-size: 14px;">
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
                            <td colspan="7" class="text-center py-5 text-muted">No recent requests found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards View --}}
            <div class="d-md-none p-3 d-flex flex-column gap-3">
                @forelse($recentRequests as $request)
                <div class="p-3 bg-light rounded-3 border mobile-req-card" id="mobile-card-{{ $request->id }}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <input class="form-check-input dashboard-req-checkbox mt-0" type="checkbox" value="{{ $request->id }}" data-tracking="{{ $request->tracking_number }}">
                            <div>
                                <span class="fw-bold text-dark font-monospace" style="font-size: 13.5px;">{{ $request->tracking_number }}</span>
                                <div class="fw-semibold text-dark mt-0.5" style="font-size: 14px;">{{ $request->resident_name }}</div>
                            </div>
                        </div>
                        <div>
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
                        </div>
                    </div>
                    
                    <div class="small text-muted mb-3 ps-4">
                        <div><i class="fa-solid fa-file-lines me-1"></i> {{ $request->document_type }}</div>
                        <div class="mt-1"><i class="fa-regular fa-calendar me-1"></i> {{ $request->created_at->format('M d, Y') }}</div>
                    </div>

                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <a href="{{ route('admin.requests') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-fill text-center">
                            <i class="fa-solid fa-eye me-1"></i> View
                        </a>
                        <form action="{{ route('admin.requests.accept', $request->id) }}" method="POST" class="m-0 p-0 flex-fill">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 w-100 text-white">
                                <i class="fa-solid fa-check me-1"></i> Approve
                            </button>
                        </form>
                        <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST" class="m-0 p-0 flex-fill" onsubmit="return confirm('Are you sure you want to reject this document request?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 w-100">
                                <i class="fa-solid fa-xmark me-1"></i> Reject
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">No recent requests found.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>

{{-- MODAL: BULK DELETE CONFIRMATION --}}
<div class="modal fade" id="dashboardBulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-trash-can fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete Selected Requests?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to permanently delete <strong id="bulk_modal_count" class="text-danger"></strong> selected document request(s)? This action cannot be undone.
                </p>
                <div class="bg-light rounded-3 p-3 text-start small mb-4 border" style="max-height: 140px; overflow-y: auto;">
                    <div class="fw-semibold text-muted mb-1 text-uppercase" style="font-size: 11px;">Selected Requests:</div>
                    <ul id="bulk_selected_names_list" class="mb-0 ps-3 text-dark font-monospace"></ul>
                </div>
                <form id="dashboardBulkDeleteForm" action="{{ route('admin.requests.bulk-delete') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <div id="bulk_hidden_ids"></div>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light border px-4 fw-semibold rounded-pill" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold rounded-pill shadow-sm">
                            <i class="fa-solid fa-trash-can me-1"></i> Yes, Delete All Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAllCheckbox = document.getElementById('selectAllDashboardReqs');
    const reqCheckboxes = document.querySelectorAll('.dashboard-req-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteModalEl = document.getElementById('dashboardBulkDeleteModal');
    const bulkDeleteModal = bulkDeleteModalEl ? new bootstrap.Modal(bulkDeleteModalEl) : null;
    const bulkHiddenIds = document.getElementById('bulk_hidden_ids');
    const bulkModalCount = document.getElementById('bulk_modal_count');
    const bulkSelectedNamesList = document.getElementById('bulk_selected_names_list');

    function updateBulkBar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.dashboard-req-checkbox:checked'));
        const count = checkedBoxes.length;

        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            selectedCountBadge.textContent = `${count} Selected`;
            bulkDeleteCount.textContent = count;
        } else {
            bulkActionsBar.classList.add('d-none');
        }

        // Highlight table rows and mobile cards
        document.querySelectorAll('.dashboard-req-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('table-primary');
                } else {
                    row.classList.remove('table-primary');
                }
            }
            const card = cb.closest('.mobile-req-card');
            if (card) {
                if (cb.checked) {
                    card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                } else {
                    card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                }
            }
        });

        if (selectAllCheckbox) {
            const allTableBoxes = document.querySelectorAll('#dashboardRequestsTable .dashboard-req-checkbox');
            const checkedTableBoxes = document.querySelectorAll('#dashboardRequestsTable .dashboard-req-checkbox:checked');
            selectAllCheckbox.checked = (allTableBoxes.length > 0 && checkedTableBoxes.length === allTableBoxes.length);
            selectAllCheckbox.indeterminate = (checkedTableBoxes.length > 0 && checkedTableBoxes.length < allTableBoxes.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            reqCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkBar();
        });
    }

    reqCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            reqCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (bulkDeleteBtn && bulkDeleteModal) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checkedBoxes = Array.from(document.querySelectorAll('.dashboard-req-checkbox:checked'));
            if (checkedBoxes.length === 0) return;

            bulkHiddenIds.innerHTML = '';
            bulkSelectedNamesList.innerHTML = '';
            bulkModalCount.textContent = `${checkedBoxes.length}`;

            // Deduplicate ids between mobile and desktop views
            const seenIds = new Set();
            checkedBoxes.forEach(cb => {
                if (seenIds.has(cb.value)) return;
                seenIds.add(cb.value);

                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'ids[]';
                hiddenInput.value = cb.value;
                bulkHiddenIds.appendChild(hiddenInput);

                const li = document.createElement('li');
                li.textContent = `Tracking #${cb.getAttribute('data-tracking') || cb.value}`;
                bulkSelectedNamesList.appendChild(li);
            });

            bulkDeleteModal.show();
        });
    }
});
</script>
@endsection
