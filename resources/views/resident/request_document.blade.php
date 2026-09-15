@extends('resident.layouts.resident')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('resident.dashboard') }}" class="text-decoration-none text-muted small mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>
        <h2 class="header-title">Request New Document</h2>
        <p class="header-subtitle">Fill out the form below to submit your document request</p>
    </div>

    <form action="{{ route('resident.request.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card card-custom p-4">
            
            <h6 class="fw-bold text-dark mb-4">Personal Information</h6>
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold">Full Name</label>
                    <input type="text" name="full_name" class="form-control px-3 py-2 border-secondary border-opacity-50" placeholder="Enter your full name">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control px-3 py-2 border-secondary border-opacity-50" placeholder="Enter your contact number">
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted small fw-semibold">Complete Address</label>
                    <input type="text" name="address" class="form-control px-3 py-2 border-secondary border-opacity-50" placeholder="Enter your complete address">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control px-3 py-2 border-secondary border-opacity-50" placeholder="Enter your email address">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold">Age</label>
                    <input type="number" name="age" class="form-control px-3 py-2 border-secondary border-opacity-50" placeholder="Enter your age">
                </div>
            </div>

            <hr class="border-light mb-5">

            <h6 class="fw-bold text-dark mb-4">Document Information</h6>
            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold">Document Type</label>
                    @php
                        $selectedDocType = request('document_type');
                        $documentTypes = [
                            'Barangay Clearance',
                            'Certificate of Indigency',
                            'Certificate of Residency',
                            'Business Clearance',
                            'Business Permit',
                            'Barangay ID',
                        ];
                    @endphp
                    <select name="document_type" class="form-select px-3 py-2 border-secondary border-opacity-50 text-dark">
                        <option value="" disabled {{ $selectedDocType ? '' : 'selected' }}>Select document type</option>
                        @foreach($documentTypes as $docType)
                            <option value="{{ $docType }}" {{ $selectedDocType === $docType ? 'selected' : '' }}>{{ $docType }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-semibold">Urgency Level</label>
                    <select name="urgency" class="form-select px-3 py-2 border-secondary border-opacity-50 text-dark">
                        <option selected disabled>Select urgency level</option>
                        <option value="Normal">Normal</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted small fw-semibold">Purpose of Request</label>
                    <textarea name="purpose" class="form-control px-3 py-2 border-secondary border-opacity-50" rows="4" placeholder="Please specify the purpose of your document request"></textarea>
                </div>
            </div>

            <h6 class="fw-bold text-dark mb-3">Supporting Documents</h6>
            <div class="border border-2 border-secondary border-opacity-50 rounded-4 p-5 text-center mb-5" style="border-style: dashed !important;">
                <div class="mb-3">
                    <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-cloud-arrow-up text-muted fs-4"></i>
                    </div>
                </div>
                <p class="mb-1 fw-semibold text-dark">Drag and drop files here, or click to browse</p>
                <p class="text-muted small mb-3">Supported formats: PDF, JPG, PNG (Max 5MB)</p>
                <button type="button" class="btn btn-primary px-4 fw-semibold rounded-3">Choose Files</button>
                <input type="file" name="attachments[]" class="d-none" multiple>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm">Submit Request</button>
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-light w-100 py-2 fw-bold rounded-3 border-secondary border-opacity-50">Save as Draft</button>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('resident.dashboard') }}" class="btn btn-white w-100 py-2 fw-bold rounded-3 border-secondary border-opacity-50 text-dark">Cancel</a>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection