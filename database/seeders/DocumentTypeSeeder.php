<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documents = [
            [
                'name'            => 'Barangay Clearance',
                'description'     => 'General clearance certifying good moral character and residency for employment, legal, and identification purposes.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => ['Valid Government-Issued ID', 'Proof of Residence'],
                'template_file'   => 'document_templates/01_Barangay_Clearance_General.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Clearance (General)',
                'description'     => 'Official general barangay clearance document for multi-purpose employment, travel, and legal requirements.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => ['Valid Government-Issued ID', 'Proof of Residence'],
                'template_file'   => 'document_templates/01_Barangay_Clearance_General (1).docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Business Clearance',
                'description'     => 'Official barangay clearance for business and trade activities located within the barangay for obtaining or renewing Mayor\'s/Business Permit.',
                'fee'             => 200.00,
                'processing_days' => '2-3 Business Days',
                'requirements'    => ['Valid Government-Issued ID', 'DTI / SEC / CDA Registration', 'Barangay Inspection Report / Lease Contract'],
                'template_file'   => 'document_templates/02_Barangay_Business_Permit.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Certification for Electric Connection',
                'description'     => 'Barangay certification interposing no objection for CEBECO II / utility electric meter installation.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Proof of Property Ownership / Lease',
                    'CEBECO II / Electric Application Form',
                ],
                'template_file'   => 'document_templates/03_Electric_Connection_Cert.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Acknowledgement Receipt',
                'description'     => 'Official barangay acknowledgement receipt issued for payments, fees, donations, and official transactions.',
                'fee'             => 0.00,
                'processing_days' => '1 Business Day',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Official Transaction Reference',
                ],
                'template_file'   => 'document_templates/04_Acknowledgement_Receipt.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Senior Citizen Certification',
                'description'     => 'Official certification certifying senior citizen status and active residency under the Barangay Senior Citizen Roster.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => [
                    'Senior Citizen ID or Birth Certificate (60+ years old)',
                    'Valid Government-Issued ID',
                    'Proof of Residence',
                ],
                'template_file'   => 'document_templates/05_Senior_Citizen_Cert.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certification for Senior Citizen Cash Assistance',
                'description'     => 'Certification verifying physical status and eligibility of a living senior citizen to claim local or national cash subsidy and financial grants.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => [
                    'Senior Citizen ID',
                    'Valid Government-Issued ID',
                    'Barangay Verification',
                ],
                'template_file'   => 'document_templates/06_Senior_Cash_Assistance.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certification for Burial Assistance',
                'description'     => 'Certification issued to authorized family members handling funeral arrangements and claiming burial financial assistance for a deceased resident.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => [
                    'Death Certificate (Certified True Copy)',
                    'Valid Government-Issued ID of Claimant',
                    'Senior Citizen ID of Deceased',
                    'Proof of Relationship',
                ],
                'template_file'   => 'document_templates/07_Senior_Burial_Assistance.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certificate of Cohabitation',
                'description'     => 'Certification attesting that common-law partners have lived together without formal marriage.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => [
                    'Valid Government-Issued ID (Both Partners)',
                    'Joint Affidavit of Cohabitation / Proof of Joint Residency',
                ],
                'template_file'   => 'document_templates/08_Certificate_of_Cohabitation.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Certification (For Probation & Bail)',
                'description'     => 'Certification attesting no derogatory blotter record for court probation supervision or bail approval.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Court Order / Subpoena / Referral Letter',
                    'Proof of Residency',
                ],
                'template_file'   => 'document_templates/10_Probation_Bail_Brgy_Cert.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Certificate',
                'description'     => 'General certification attesting to good moral standing, residency, and character.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => [
                    'Valid Government-Issued ID',
                    'Proof of Residency',
                ],
                'template_file'   => 'document_templates/11_General_Barangay_Certificate.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Barangay Certification (R.A. 11261 - First Time Jobseeker)',
                'description'     => 'Free official certification for first time jobseekers pursuant to Republic Act No. 11261.',
                'fee'             => 0.00,
                'processing_days' => '1 Business Day',
                'requirements'    => [
                    'Valid ID / School ID / PSA Birth Certificate',
                    'Signed Oath of Undertaking',
                ],
                'template_file'   => 'document_templates/12_First_Time_Jobseeker_Cert.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Oath of Undertaking (R.A. 11261)',
                'description'     => 'Sworn oath of undertaking executed by first time jobseekers pursuant to Republic Act No. 11261.',
                'fee'             => 0.00,
                'processing_days' => '1 Business Day',
                'requirements'    => [
                    'Valid ID / School ID / PSA Birth Certificate',
                    'Signed Oath of Undertaking',
                ],
                'template_file'   => 'document_templates/13_Oath_of_Undertaking_Jobseeker.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certificate of Indigency',
                'description'     => 'Certifies that the resident belongs to an indigent family for medical, educational, or legal assistance.',
                'fee'             => 0.00,
                'processing_days' => 'Same Day',
                'requirements'    => ['Valid Government-Issued ID', 'Proof of Low / No Income'],
                'template_file'   => 'document_templates/indigency_template.docx',
                'is_active'       => true,
            ],
            [
                'name'            => 'Certificate of Residency',
                'description'     => 'Official certification confirming bona fide residency in the barangay jurisdiction.',
                'fee'             => 50.00,
                'processing_days' => '1-2 Business Days',
                'requirements'    => ['Valid Government-Issued ID', 'Proof of Residence'],
                'template_file'   => 'document_templates/residency_template.docx',
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
}

