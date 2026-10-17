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
                'name'            => 'Certificate of Residency',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'description'     => 'Official certification attesting permanent and bona fide residence in Barangay Cogon East.',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Proof of Residency (e.g. Utility Bill, Barangay Record)',
                ],
                'template_file'   => 'residency_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certificate of Indigency',
                'fee'             => 0.00,
                'processing_days' => '1-2 Business Days',
                'description'     => 'Official certification attesting that the resident belongs to an indigent family in need of assistance.',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Proof of Low / No Income',
                ],
                'template_file'   => 'indigency_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Certification (For Probation & Bail)',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'description'     => 'Certification attesting no derogatory blotter record for court probation supervision or bail approval.',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Court Order / Subpoena / Referral Letter',
                    'Proof of Residency',
                ],
                'template_file'   => 'probation_bail_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Oath of Undertaking (R.A. 11261)',
                'fee'             => 0.00,
                'processing_days' => '1 Business Day',
                'description'     => 'Sworn oath of undertaking executed by first time jobseekers pursuant to Republic Act No. 11261.',
                'requirements'    => [
                    'Valid ID / School ID / PSA Birth Certificate',
                    'Signed Oath of Undertaking',
                ],
                'template_file'   => 'oath_of_undertaking_template.docx',
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
            'Barangay Certification (For Probation & Bail)',
            'Oath of Undertaking (R.A. 11261)',
        ];

        DocumentType::whereIn('name', $names)->delete();
    }
};
