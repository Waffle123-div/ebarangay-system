@extends('resident.layouts.resident')

@section('content')
<div class="container py-4">

    {{-- Top Action Bar --}}
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <a href="{{ route('resident.request.track') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Track Requests
            </a>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm">
                <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    {{-- Official Printable Receipt Document --}}
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden mx-auto printable-receipt" style="max-width: 800px; background: #fff;">
        
        {{-- Receipt Header --}}
        <div class="p-4 text-center border-bottom bg-light position-relative" style="border-bottom: 2px solid #000 !important;">
            <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 50px; height: 50px; font-size: 22px;">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <div class="text-start">
                    <div class="text-uppercase text-muted fw-bold small" style="letter-spacing: 1px; font-size: 11px;">Republic of the Philippines</div>
                    <h5 class="fw-bold text-dark mb-0">Barangay Cogon East</h5>
                    <div class="text-muted small">E-Barangay System — Official Transaction Receipt</div>
                </div>
            </div>
        </div>

        <div class="card-body p-4 p-md-5">

            {{-- Receipt Metadata --}}
            <div class="row g-3 mb-4 pb-3 border-bottom">
                <div class="col-sm-6">
                    <span class="text-muted small d-block">RECEIPT NUMBER</span>
                    <h5 class="fw-bold text-primary mb-0 font-monospace">{{ $receiptNumber }}</h5>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <span class="text-muted small d-block">DATE & TIME</span>
                    <span class="fw-bold text-dark">{{ $docRequest->created_at->format('F d, Y — h:i A') }}</span>
                </div>
            </div>

            {{-- Resident Details --}}
            <div class="bg-light rounded-3 p-3 mb-4 border">
                <h6 class="fw-bold text-dark mb-3 text-uppercase small" style="letter-spacing: 0.5px;">Resident Information</h6>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <span class="text-muted small">Full Name:</span>
                        <div class="fw-bold text-dark">{{ $docRequest->resident_name }}</div>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small">Email Address:</span>
                        <div class="fw-bold text-dark">{{ $docRequest->resident_email ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6 mt-2">
                        <span class="text-muted small">User ID:</span>
                        <div class="fw-bold text-dark font-monospace">{{ $user->user_id ?? 'N/A' }}</div>
                    </div>
                    <div class="col-sm-6 mt-2">
                        <span class="text-muted small">Barangay:</span>
                        <div class="fw-bold text-dark">Barangay Cogon East</div>
                    </div>
                </div>
            </div>

            {{-- Request Details & Fee Table --}}
            <h6 class="fw-bold text-dark mb-3 text-uppercase small" style="letter-spacing: 0.5px;">Transaction Details</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-2 text-muted small fw-bold">TRACKING NO.</th>
                            <th class="py-2 text-muted small fw-bold">DOCUMENT TYPE</th>
                            <th class="py-2 text-muted small fw-bold">PURPOSE</th>
                            <th class="py-2 text-muted small fw-bold text-end">FEE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold text-dark font-monospace py-3">{{ $docRequest->tracking_number }}</td>
                            <td class="fw-bold text-primary py-3">{{ $docRequest->document_type }}</td>
                            <td class="text-muted small py-3">{{ $docRequest->purpose ?? 'General Purpose' }}</td>
                            <td class="fw-bold text-dark text-end py-3">
                                @if($feeAmount > 0)
                                    ₱{{ number_format($feeAmount, 2) }}
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">FREE</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Summary & Status --}}
            <div class="row align-items-center mb-4">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <div class="p-3 rounded-3 border bg-light">
                        <span class="text-muted small d-block mb-1">DOCUMENT STATUS</span>
                        @if($docRequest->status == 'Completed')
                            <span class="badge bg-success text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> Completed / Claimed</span>
                        @elseif($docRequest->status == 'Ready')
                            <span class="badge bg-info text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-bell me-1"></i> Ready for Pickup</span>
                        @elseif($docRequest->status == 'Rejected')
                            <span class="badge bg-danger text-white px-3 py-2 rounded-pill"><i class="fa-solid fa-xmark me-1"></i> Rejected</span>
                        @else
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fa-solid fa-clock me-1"></i> {{ $docRequest->status }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6 text-sm-end">
                    <div class="p-3 rounded-3 border bg-light">
                        <span class="text-muted small d-block mb-1">TOTAL AMOUNT</span>
                        <h4 class="fw-bold text-success mb-0">
                            @if($feeAmount > 0)
                                ₱{{ number_format($feeAmount, 2) }}
                            @else
                                FREE OF CHARGE
                            @endif
                        </h4>
                    </div>
                </div>
            </div>

            {{-- Footer Note & QR Code Simulation --}}
            <div class="border-top pt-4 mt-4 text-center">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-start">
                        <div class="fw-bold text-dark small"><i class="fa-solid fa-shield-halved text-primary me-1"></i> Verified E-Barangay Transaction</div>
                        <div class="text-muted" style="font-size: 11px;">This receipt is generated automatically by the E-Barangay System.</div>
                    </div>
                    <div class="text-end font-monospace text-muted small" style="font-size: 10px;">
                        SYS-VERIFIED: {{ md5($receiptNumber . $docRequest->tracking_number) }}
                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom Banner --}}
        <div class="bg-primary text-white text-center py-2 small fw-semibold">
            Thank you for using the E-Barangay System — Barangay Cogon East
        </div>

    </div>

</div>

<style>
@media print {
    /* Hide top navbar, back button, sidebar, and rest of UI */
    .top-navbar, .sidebar-wrapper, #sidebar-wrapper, .no-print, nav, footer, .btn {
        display: none !important;
    }
    body {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .printable-receipt {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
    }
    .container-fluid, .container {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
}
</style>
@endsection
