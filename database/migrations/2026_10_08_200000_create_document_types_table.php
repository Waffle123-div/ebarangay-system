<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('fee', 10, 2)->default(0.00);
            $table->string('processing_days')->default('1-2 Business Days');
            $table->json('requirements')->nullable();
            $table->string('template_file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed standard Barangay documents
        $defaultTypes = [
            [
                'name'            => 'Barangay Clearance',
                'description'     => 'General clearance certifying good moral character and residency for employment, legal, and identification purposes.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Proof of Residence']),
                'template_file'   => 'clearance_template.docx',
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Certificate of Residency',
                'description'     => 'Official certification confirming bona fide residency in the barangay jurisdiction.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Proof of Residence']),
                'template_file'   => 'residency_template.docx',
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Certificate of Indigency',
                'description'     => 'Certifies that the resident belongs to an indigent family for medical, educational, or legal assistance.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID']),
                'template_file'   => 'indigency_template.docx',
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Brgy Clearance for Business Permit',
                'description'     => 'Clearance required by the City/Municipality for applying or renewing a business permit.',
                'fee'             => 200.00,
                'processing_days' => '2-3 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'DTI / SEC Registration', 'Barangay Inspection Report']),
                'template_file'   => 'business_permit_template.docx',
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Medical Assistance',
                'description'     => 'Barangay endorsement or certificate for hospital bills, medical aid, or PCSO/DSWD assistance.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Medical Certificate or Prescription', 'Hospital Bill / Clinical Abstract']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'First Time Job Seeker',
                'description'     => 'Certificate issued under Republic Act 11261 waiving government fees for first-time jobseekers.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Signed Oath of Undertaking', 'Barangay Clearance']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Oath of Undertaking Job Seeker',
                'description'     => 'Affidavit / oath form required under the First-Time Jobseekers Act (RA 11261).',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Signed Oath of Undertaking Form']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Barangay Certificate',
                'description'     => 'General certification of good standing and community residency.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Proof of Residence']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Cohabitation Certificate',
                'description'     => 'Certifies that a couple has been cohabiting as husband and wife without legal impediment to marry.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Proof of Shared Residence', 'Affidavit of 2 Disinterested Persons']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Electric Certificate',
                'description'     => 'Barangay certificate for Meralco/electric power connection application.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Proof of Ownership or Landlord Consent']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Certification for Senior Citizen',
                'description'     => 'Endorsement certifying senior citizen residency for OSCA benefits and discounts.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Senior Citizen ID or Birth Certificate (60+ yo)']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Cash Assistance Senior Certificate',
                'description'     => 'Certificate required for social pension and local cash subsidy applications for seniors.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Senior Citizen ID']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Burial Senior Citizen Certificate',
                'description'     => 'Barangay certification for claiming burial and mortuary assistance for deceased senior citizens.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Registered Death Certificate', 'Senior Citizen ID']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Scholarship Indigency',
                'description'     => 'Certificate of indigency specifically formatted for government and private scholarship applications.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'School Enrollment Form or Acceptance Letter', 'Report Card / TOR']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Indigency Bail Certificate',
                'description'     => 'Indigency certification for PAO legal assistance and court bail reduction requests.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Court Order or PAO Referral']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Bail Certificate',
                'description'     => 'Barangay clearance and verification of residence for court bail requirements.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Court Order or Case Information']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Probation Barangay Certificate',
                'description'     => 'Certificate of good standing for parole and probation administration monitoring.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => json_encode(['Valid Government-Issued ID', 'Probation Office Referral']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'name'            => 'Acknowledgement Receipt',
                'description'     => 'Barangay acknowledgement receipt for document and notice receiving.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => json_encode(['Valid Government-Issued ID']),
                'template_file'   => null,
                'is_active'       => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ];

        DB::table('document_types')->insert($defaultTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
