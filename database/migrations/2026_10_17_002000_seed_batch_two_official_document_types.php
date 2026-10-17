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
                'name'            => 'Barangay Certification for Electric Connection',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'description'     => 'Barangay certification interposing no objection for CEBECO II electric meter installation.',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Proof of Property Ownership / Lease',
                    'CEBECO II Application Form',
                ],
                'template_file'   => 'electric_connection_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Acknowledgement Receipt',
                'fee'             => 0.00,
                'processing_days' => '1 Business Day',
                'description'     => 'Official barangay acknowledgement receipt issued for payments, fees, and transactions.',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Official Transaction Reference',
                ],
                'template_file'   => 'acknowledgement_receipt_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certificate of Cohabitation',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'description'     => 'Certification attesting that common-law partners have lived together without formal marriage.',
                'requirements'    => [
                    'Valid Government-Issued ID (Both Partners)',
                    'Joint Affidavit of Cohabitation / Proof of Joint Residency',
                ],
                'template_file'   => 'cohabitation_certificate_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Certificate',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'description'     => 'General certification attesting to good moral standing, residency, and character.',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Proof of Residency',
                ],
                'template_file'   => 'barangay_certificate_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Certification (R.A. 11261 - First Time Jobseeker)',
                'fee'             => 0.00,
                'processing_days' => '1 Business Day',
                'description'     => 'Free official certification for first time jobseekers pursuant to Republic Act No. 11261.',
                'requirements'    => [
                    'Valid ID / School ID / PSA Birth Certificate',
                    'Signed Oath of Undertaking',
                ],
                'template_file'   => 'first_time_jobseeker_template.docx',
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
        $names = [
            'Barangay Certification for Electric Connection',
            'Acknowledgement Receipt',
            'Certificate of Cohabitation',
            'Barangay Certificate',
            'Barangay Certification (R.A. 11261 - First Time Jobseeker)',
        ];

        DocumentType::whereIn('name', $names)->delete();
    }
};
