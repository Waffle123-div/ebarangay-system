@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">

    <style>
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

        .table-logs {
            width: 100%;
            min-width: 820px;
        }

        @media (max-width: 767.98px) {
            .mobile-log-card {
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
        <h2 class="header-title">Activity Logs</h2>
        <p class="header-subtitle mb-0">A chronological record of all admin and system actions.</p>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 p-sm-4">
            <form method="GET" action="{{ route('admin.activity-logs') }}" class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label text-muted small fw-semibold">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <i class="fa-solid fa-magnifying-glass fa-sm"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="User name, description…" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
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
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small fw-semibold">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small fw-semibold">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-12 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill" title="Apply Filter"><i class="fa-solid fa-filter"></i></button>
                    <a href="{{ route('admin.activity-logs') }}" class="btn btn-light border flex-fill text-center" title="Clear Filters"><i class="fa-solid fa-xmark"></i></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white d-flex align-items-center justify-content-between px-3 px-sm-4 py-3 border-bottom flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Log Entries
            </h6>
            <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 11.5px;">
                {{ $logs->total() }} total records
            </span>
        </div>
        
        {{-- Bulk Selection Bar --}}
        <div id="bulkActionsBar" class="d-none bg-primary bg-opacity-10 border-bottom px-3 px-md-4 py-2.5 d-flex align-items-center justify-content-between flex-wrap gap-2 transition-all">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge">0 Selected</span>
                <span class="text-muted small d-none d-sm-inline">Select multiple activity logs to delete in bulk.</span>
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
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0 table-logs" id="logsMasterTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-center border-0" style="width: 44px;">
                                <input class="form-check-input" type="checkbox" id="selectAllLogs" title="Select All">
                            </th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px; width: 60px;">#</th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px; min-width: 170px;">USER</th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px; width: 150px;">ACTION</th>
                            <th class="py-3 text-muted small fw-bold border-0" style="letter-spacing:0.5px; min-width: 250px;">DESCRIPTION</th>
                            <th class="py-3 text-muted small fw-bold border-0 pe-4 text-nowrap" style="letter-spacing:0.5px; width: 140px;">DATE & TIME</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($logs as $log)
                        <tr id="log-row-{{ $log->id }}">
                            <td class="ps-4 text-center py-3" style="width: 44px;">
                                <input class="form-check-input log-checkbox" type="checkbox" value="{{ $log->id }}" data-desc="{{ $log->description }}">
                            </td>
                            <td class="text-muted small border-bottom py-3">{{ $log->id }}</td>
                            <td class="border-bottom py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold flex-shrink-0" style="width:34px;height:34px;font-size:13px;">
                                        {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY' }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size:14px; max-width: 150px;">{{ $log->user->name ?? 'System' }}</div>
                                        <div class="text-muted text-truncate" style="font-size:12px;">{{ $log->user->role ?? '–' }}</div>
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
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-2.5 py-1 rounded-pill fw-semibold" style="font-size:11.5px;">
                                    {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="border-bottom py-3 text-dark" style="font-size:13.5px;">
                                {{ $log->description }}
                            </td>
                            <td class="border-bottom py-3 pe-4 text-muted small text-nowrap">
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

            {{-- Mobile Cards View (Screens < 768px) --}}
            <div class="d-md-none p-3" id="logs-mobile-list">
                @forelse($logs as $log)
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
                    <div class="mobile-log-card" id="mobile-log-card-{{ $log->id }}">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <input class="form-check-input log-checkbox mt-0" type="checkbox" value="{{ $log->id }}" data-desc="{{ $log->description }}">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold flex-shrink-0" style="width: 30px; height: 30px; font-size: 11.5px;">
                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 2)) : 'SY' }}
                                </div>
                                <span class="fw-bold text-dark" style="font-size: 13.5px;">{{ $log->user->name ?? 'System' }}</span>
                            </div>
                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 11px;">
                                {{ ucwords(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </div>
                        <div class="text-dark my-2 ps-4" style="font-size: 13px;">
                            {{ $log->description }}
                        </div>
                        <div class="d-flex justify-content-end align-items-center text-muted small pt-2 border-top" style="font-size: 11.5px;">
                            <span><i class="fa-regular fa-clock me-1"></i> {{ $log->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-clock-rotate-left fa-3x mb-2 text-light d-block"></i>
                        <h6 class="fw-bold text-dark">No Activity Logs Found</h6>
                        <div class="small">Actions performed in the admin panel will appear here.</div>
                    </div>
                @endforelse
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-white border-top px-3 px-sm-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }} entries
            </div>
            <div class="pagination-wrapper">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>

</div>

{{-- MODAL: BULK DELETE CONFIRMATION FOR ACTIVITY LOGS --}}
<div class="modal fade" id="logsBulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-trash-can fs-4"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete Selected Activity Logs?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to permanently delete <strong id="bulk_modal_count" class="text-danger"></strong> selected activity log entry(s)? This action cannot be undone.
                </p>
                <div class="bg-light rounded-3 p-3 text-start small mb-4 border" style="max-height: 140px; overflow-y: auto;">
                    <div class="fw-semibold text-muted mb-1 text-uppercase" style="font-size: 11px;">Selected Logs:</div>
                    <ul id="bulk_selected_names_list" class="mb-0 ps-3 text-dark font-monospace" style="font-size: 12px;"></ul>
                </div>
                <form id="logsBulkDeleteForm" action="{{ route('admin.activity-logs.bulk-delete') }}" method="POST" class="d-inline">
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
    const selectAllCheckbox = document.getElementById('selectAllLogs');
    const logCheckboxes = document.querySelectorAll('.log-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const deselectAllBtn = document.getElementById('deselectAllBtn');
    const bulkDeleteModalEl = document.getElementById('logsBulkDeleteModal');
    const bulkDeleteModal = bulkDeleteModalEl ? new bootstrap.Modal(bulkDeleteModalEl) : null;
    const bulkHiddenIds = document.getElementById('bulk_hidden_ids');
    const bulkModalCount = document.getElementById('bulk_modal_count');
    const bulkSelectedNamesList = document.getElementById('bulk_selected_names_list');

    function updateBulkBar() {
        const checkedBoxes = Array.from(document.querySelectorAll('.log-checkbox:checked'));
        const count = checkedBoxes.length;

        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            selectedCountBadge.textContent = `${count} Selected`;
            bulkDeleteCount.textContent = count;
        } else {
            bulkActionsBar.classList.add('d-none');
        }

        // Highlight table rows and mobile cards
        document.querySelectorAll('.log-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                if (cb.checked) {
                    row.classList.add('table-primary');
                } else {
                    row.classList.remove('table-primary');
                }
            }
            const card = cb.closest('.mobile-log-card');
            if (card) {
                if (cb.checked) {
                    card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
                } else {
                    card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                }
            }
        });

        if (selectAllCheckbox) {
            const allTableBoxes = document.querySelectorAll('#logsMasterTable .log-checkbox');
            const checkedTableBoxes = document.querySelectorAll('#logsMasterTable .log-checkbox:checked');
            selectAllCheckbox.checked = (allTableBoxes.length > 0 && checkedTableBoxes.length === allTableBoxes.length);
            selectAllCheckbox.indeterminate = (checkedTableBoxes.length > 0 && checkedTableBoxes.length < allTableBoxes.length);
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            logCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkBar();
        });
    }

    logCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function () {
            logCheckboxes.forEach(cb => cb.checked = false);
            updateBulkBar();
        });
    }

    if (bulkDeleteBtn && bulkDeleteModal) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checkedBoxes = Array.from(document.querySelectorAll('.log-checkbox:checked'));
            if (checkedBoxes.length === 0) return;

            bulkHiddenIds.innerHTML = '';
            bulkSelectedNamesList.innerHTML = '';
            bulkModalCount.textContent = `${checkedBoxes.length}`;

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
                li.textContent = `Log #${cb.value}: ${(cb.getAttribute('data-desc') || '').substring(0, 70)}`;
                bulkSelectedNamesList.appendChild(li);
            });

            bulkDeleteModal.show();
        });
    }
});
</script>
@endsection