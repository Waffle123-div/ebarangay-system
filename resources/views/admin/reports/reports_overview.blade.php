@extends('admin.layouts.admin')

@section('content')
<div class="container-fluid px-3 px-sm-4 py-3 py-sm-4">
    <div class="mb-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
        <div>
            <h2 class="header-title mb-1">System Reports & Analytics</h2>
            <p class="header-subtitle mb-0">Overview of statistics and document issuances for Carmen</p>
        </div>
        <div>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm w-100 w-sm-auto">
                <i class="fa-solid fa-file-export me-2"></i> Generate Monthly Report
            </button>
        </div>
    </div>

    <div class="row g-3 g-sm-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3 p-sm-4 border-0 shadow-sm rounded-4">
                <div class="text-muted small fw-semibold mb-1 text-truncate">Total Documents Issued</div>
                <div class="h3 fw-bold mb-1 text-dark">1,842</div>
                <div class="small"><span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> 8.5%</span> <span class="text-muted d-none d-sm-inline">vs last month</span></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3 p-sm-4 border-0 shadow-sm rounded-4">
                <div class="text-muted small fw-semibold mb-1 text-truncate">Barangay Revenue</div>
                <div class="h3 fw-bold mb-1 text-dark">₱ 42,500</div>
                <div class="small"><span class="text-success fw-bold"><i class="fa fa-arrow-up"></i> 12%</span> <span class="text-muted d-none d-sm-inline">vs last month</span></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3 p-sm-4 border-0 shadow-sm rounded-4">
                <div class="text-muted small fw-semibold mb-1 text-truncate">Active Disputes</div>
                <div class="h3 fw-bold mb-1 text-danger">14</div>
                <div class="small"><span class="text-danger fw-bold"><i class="fa fa-arrow-up"></i> 2</span> <span class="text-muted d-none d-sm-inline">new this week</span></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 p-3 p-sm-4 border-0 shadow-sm rounded-4">
                <div class="text-muted small fw-semibold mb-1 text-truncate">Registered Residents</div>
                <div class="h3 fw-bold mb-1 text-primary">8,405</div>
                <div class="small text-muted text-truncate">Updated 2 days ago</div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-sm-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header border-0 bg-white pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Document Requests by Type</h5>
                    <small class="text-muted">Current Year (2026)</small>
                </div>
                <div class="card-body px-4 pt-4 pb-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold text-dark">Barangay Clearance</span>
                            <span class="small text-muted">45% (828)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold text-dark">Certificate of Indigency</span>
                            <span class="small text-muted">30% (552)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold text-dark">Business Permit Clearance</span>
                            <span class="small text-muted">15% (276)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-0">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-semibold text-dark">Certificate of Residency</span>
                            <span class="small text-muted">10% (186)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header border-0 bg-white pt-4 px-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Recent Report Logs</h5>
                    <button class="btn btn-sm btn-light border rounded-pill px-3">View All</button>
                </div>
                
                {{-- Desktop Table View --}}
                <div class="d-none d-sm-block table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="text-muted small" style="background-color: #f8f9fe;">
                            <tr>
                                <th class="ps-4 py-3 border-0">REPORT NAME</th>
                                <th class="py-3 border-0">DATE</th>
                                <th class="pe-4 py-3 border-0 text-end">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-danger bg-opacity-10 p-2 rounded text-danger">
                                            <i class="fa-solid fa-file-pdf fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size: 14px;">March 2026 Summary</div>
                                            <div class="text-muted" style="font-size: 12px;">Generated by Admin</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted py-3">Mar 01, 2026</td>
                                <td class="pe-4 py-3 text-end"><button class="btn btn-sm btn-link text-decoration-none text-primary fw-bold p-0">Download</button></td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-success bg-opacity-10 p-2 rounded text-success">
                                            <i class="fa-solid fa-file-excel fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size: 14px;">Q1 Financial Collection</div>
                                            <div class="text-muted" style="font-size: 12px;">Generated by Treasurer</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted py-3">Feb 28, 2026</td>
                                <td class="pe-4 py-3 text-end"><button class="btn btn-sm btn-link text-decoration-none text-primary fw-bold p-0">Download</button></td>
                            </tr>
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-danger bg-opacity-10 p-2 rounded text-danger">
                                            <i class="fa-solid fa-file-pdf fa-lg"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark" style="font-size: 14px;">Voter Demographics</div>
                                            <div class="text-muted" style="font-size: 12px;">Generated by Sec. Santos</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted py-3">Feb 15, 2026</td>
                                <td class="pe-4 py-3 text-end"><button class="btn btn-sm btn-link text-decoration-none text-primary fw-bold p-0">Download</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="d-sm-none p-3 d-flex flex-column gap-2">
                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger bg-opacity-10 p-2 rounded text-danger">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size: 13.5px;">March 2026 Summary</div>
                                <div class="text-muted" style="font-size: 11px;">Mar 01, 2026 &bull; Admin</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>

                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 p-2 rounded text-success">
                                <i class="fa-solid fa-file-excel fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size: 13.5px;">Q1 Financial Collection</div>
                                <div class="text-muted" style="font-size: 11px;">Feb 28, 2026 &bull; Treasurer</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>

                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger bg-opacity-10 p-2 rounded text-danger">
                                <i class="fa-solid fa-file-pdf fa-lg"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size: 13.5px;">Voter Demographics</div>
                                <div class="text-muted" style="font-size: 11px;">Feb 15, 2026 &bull; Sec. Santos</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                            <i class="fa-solid fa-download"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection