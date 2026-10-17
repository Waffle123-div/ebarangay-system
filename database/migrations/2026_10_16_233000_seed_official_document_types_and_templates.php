<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\DocumentType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $documents = [
            [
                'name'            => 'Barangay Clearance',
                'description'     => 'General clearance certifying good moral character and residency for employment, legal, and identification purposes.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => ['Valid Government-Issued ID', 'Proof of Residence'],
                'template_file'   => 'clearance_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Senior Citizen Certification',
                'description'     => 'Official certification certifying senior citizen status and active residency under the Barangay Senior Citizen Roster.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => ['Senior Citizen ID or Birth Certificate (60+ years old)', 'Valid Government-Issued ID', 'Proof of Residence'],
                'template_file'   => 'senior_citizen_certification_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certification for Senior Citizen Cash Assistance',
                'description'     => 'Certification verifying physical status and eligibility of a living senior citizen to claim local or national cash subsidy and financial grants.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => ['Senior Citizen ID', 'Valid Government-Issued ID', 'Barangay Verification'],
                'template_file'   => 'senior_citizen_cash_assistance_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certification for Burial Assistance',
                'description'     => 'Certification issued to authorized family members handling funeral arrangements and claiming burial financial assistance for a deceased senior citizen resident.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => ['Death Certificate (Certified True Copy)', 'Valid Government-Issued ID of Claimant', 'Senior Citizen ID of Deceased', 'Proof of Relationship'],
                'template_file'   => 'burial_assistance_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Business Clearance',
                'description'     => 'Official barangay clearance for business and trade activities located within the barangay for obtaining or renewing Mayor\'s/Business Permit.',
                'fee'             => 200.00,
                'processing_days' => '2-3 Business Days',
                'requirements'    => ['Valid Government-Issued ID', 'DTI / SEC / CDA Registration', 'Barangay Inspection Report / Lease Contract'],
                'template_file'   => 'business_clearance_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certificate of Residency',
                'description'     => 'Official certification confirming bona fide residency in the barangay jurisdiction.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => ['Valid Government-Issued ID', 'Proof of Residence'],
                'template_file'   => 'residency_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certificate of Indigency',
                'description'     => 'Certifies that the resident belongs to an indigent family for medical, educational, or legal assistance.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => ['Valid Government-Issued ID'],
                'template_file'   => 'indigency_template.docx',
                'is_active'       => true,
            ],
        ];

        foreach ($documents as $doc) {
            DocumentType::updateOrCreate(
                ['name' => $doc['name']],
                $doc
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DocumentType::whereIn('name', [
            'Senior Citizen Certification',
            'Certification for Senior Citizen Cash Assistance',
            'Certification for Burial Assistance',
            'Barangay Business Clearance',
        ])->delete();
    }
};
