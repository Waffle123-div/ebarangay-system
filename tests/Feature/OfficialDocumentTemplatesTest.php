<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OfficialDocumentTemplatesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);
    }

    protected function createAdmin(): User
    {
        return User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Admin Officer',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_' . uniqid() . '@example.com',
            'role'              => 'Admin',
            'status'            => 'Active',
            'email_verified_at' => now(),
            'security_pin'      => Hash::make('1234'),
            'password'          => Hash::make('password'),
        ]);
    }

    protected function createResident(): User
    {
        return User::create([
            'user_id'           => 'RES-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Juan Dela Cruz',
            'first_name'        => 'Juan',
            'last_name'         => 'Dela Cruz',
            'role'              => 'Resident',
            'status'            => 'Active',
            'civil_status'      => 'Single',
            'age'               => 65,
            'purok'             => 'Purok 1',
            'email'             => 'resident_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'security_pin'      => Hash::make('1234'),
            'password'          => Hash::make('password'),
        ]);
    }

    public function test_all_official_document_types_exist_and_are_active(): void
    {
        $expectedDocs = [
            'Barangay Clearance' => 'clearance_template.docx',
            'Senior Citizen Certification' => 'senior_citizen_certification_template.docx',
            'Certification for Senior Citizen Cash Assistance' => 'senior_citizen_cash_assistance_template.docx',
            'Certification for Burial Assistance' => 'burial_assistance_template.docx',
            'Barangay Business Clearance' => 'business_clearance_template.docx',
            'Barangay Certification for Electric Connection' => 'electric_connection_template.docx',
            'Acknowledgement Receipt' => 'acknowledgement_receipt_template.docx',
            'Certificate of Cohabitation' => 'cohabitation_certificate_template.docx',
            'Barangay Certificate' => 'barangay_certificate_template.docx',
            'Barangay Certification (R.A. 11261 - First Time Jobseeker)' => 'first_time_jobseeker_template.docx',
            'Certificate of Residency' => 'residency_template.docx',
            'Certificate of Indigency' => 'indigency_template.docx',
            'Barangay Certification (For Probation & Bail)' => 'probation_bail_template.docx',
            'Oath of Undertaking (R.A. 11261)' => 'oath_of_undertaking_template.docx',
        ];

        foreach ($expectedDocs as $name => $templateFile) {
            $docType = DocumentType::where('name', $name)->first();
            $this->assertNotNull($docType, "DocumentType '{$name}' must exist in the database.");
            $this->assertTrue((bool)$docType->is_active, "DocumentType '{$name}' must be active.");
            $this->assertNotEmpty($docType->icon, "DocumentType '{$name}' must have an icon.");
            $this->assertFileExists(storage_path('app/' . $templateFile), "Template file '{$templateFile}' must exist in storage/app.");
        }
    }

    public function test_resident_request_document_page_lists_all_official_document_types(): void
    {
        $resident = $this->createResident();

        $response = $this->actingAs($resident)
            ->withSession(['pin_verified' => true])
            ->get(route('resident.request.create'));

        $response->assertStatus(200);

        $response->assertSee('Barangay Clearance');
        $response->assertSee('Senior Citizen Certification');
        $response->assertSee('Certification for Senior Citizen Cash Assistance');
        $response->assertSee('Certification for Burial Assistance');
        $response->assertSee('Barangay Business Clearance');
        $response->assertSee('Barangay Certification for Electric Connection');
        $response->assertSee('Acknowledgement Receipt');
        $response->assertSee('Certificate of Cohabitation');
        $response->assertSee('Barangay Certificate');
        $response->assertSee('Barangay Certification (R.A. 11261 - First Time Jobseeker)');
        $response->assertSee('Certificate of Residency');
        $response->assertSee('Certificate of Indigency');
        $response->assertSee('Barangay Certification (For Probation & Bail)');
        $response->assertSee('Oath of Undertaking (R.A. 11261)');
    }

    public function test_all_official_templates_generate_and_replace_placeholders_cleanly(): void
    {
        $admin = $this->createAdmin();
        $resident = $this->createResident();

        \App\Models\BarangaySetting::set('barangay_captain', 'Hon. Crisostomo Ibarra', 'barangay');
        \App\Models\BarangaySetting::set('barangay_secretary', 'Maria Clara', 'barangay');
        \App\Models\BarangaySetting::set('barangay_treasurer', 'Kapitan Tiago', 'barangay');

        $docConfigs = [
            'Barangay Clearance' => [],
            'Senior Citizen Certification' => ['birthdate' => '1958-05-12'],
            'Certification for Senior Citizen Cash Assistance' => [],
            'Certification for Burial Assistance' => [
                'deceased_name' => 'PEDRO PENDUKO',
                'date_of_death' => 'October 01, 2026',
                'relationship'  => 'SON',
            ],
            'Barangay Business Clearance' => [
                'business_name'    => 'COGON EAST CONVENIENCE STORE',
                'business_address' => 'Purok 2, Barangay Cogon East, Carmen, Cebu',
            ],
            'Barangay Certification for Electric Connection' => [
                'purpose' => 'Electric Meter Connection',
            ],
            'Acknowledgement Receipt' => [
                'purpose' => 'Official Clearance Fee',
                'fee'     => 125.50,
            ],
            'Certificate of Cohabitation' => [
                'purpose'            => 'Housing Application',
                'partner_name'       => 'MARIA CLARA DE LOS SANTOS',
                'years_cohabitating' => '6',
            ],
            'Barangay Certificate' => [
                'purpose' => 'Bank Account Requirement',
            ],
            'Barangay Certification (R.A. 11261 - First Time Jobseeker)' => [
                'residence_years' => '8',
            ],
            'Certificate of Residency' => [
                'purpose' => 'Job Application Requirement',
            ],
            'Certificate of Indigency' => [
                'purpose' => 'Medical and Financial Assistance',
            ],
            'Barangay Certification (For Probation & Bail)' => [
                'purpose' => 'Court Bail Approval',
            ],
            'Oath of Undertaking (R.A. 11261)' => [
                'purpose' => 'First Time Jobseekers Act Benefit',
            ],
        ];

        foreach ($docConfigs as $docName => $extras) {
            $docType = DocumentType::where('name', $docName)->first();
            $tracking = 'REQ-TEST-' . strtoupper(substr(uniqid(), -6));

            $fee = isset($extras['fee']) ? $extras['fee'] : 50.00;

            $reqData = array_merge([
                'user_id'          => $resident->id,
                'tracking_number'  => $tracking,
                'first_name'       => 'JUAN',
                'last_name'        => 'DELA CRUZ',
                'document_type_id' => $docType?->id,
                'document_type'    => $docName,
                'purpose'          => 'OFFICIAL TRANSACTION',
                'purok'            => 'Purok 4',
                'age'              => 67,
                'civil_status'     => 'MARRIED',
                'fee'              => $fee,
                'status'           => 'Processing',
                'documents'        => [
                    [
                        'type'         => $docName,
                        'purpose'      => 'OFFICIAL TRANSACTION',
                        'fee'          => $fee,
                        'is_generated' => false,
                    ]
                ],
            ], $extras);

            $docRequest = DocumentRequest::create($reqData);

            $response = $this->actingAs($admin)->get(route('admin.requests.generate', $docRequest->id));
            $response->assertStatus(200);

            $streamedContent = $response->streamedContent();
            $tempOutput = tempnam(sys_get_temp_dir(), 'test_doc_') . '.docx';
            file_put_contents($tempOutput, $streamedContent);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($tempOutput) === true, "Generated file for '{$docName}' must be a valid zip/docx archive.");
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();

            // Verify no unreplaced placeholders remain in the document body
            preg_match_all('/\$\{([^}]+)\}/', $xml, $matches);
            $unreplaced = array_unique($matches[0]);
            $this->assertEmpty($unreplaced, "Generated document for '{$docName}' should have zero unreplaced tags, but had: " . implode(', ', $unreplaced));

            // Verify key data exists in the document
            $this->assertStringContainsString('JUAN DELA CRUZ', $xml, "Document '{$docName}' should contain resident name JUAN DELA CRUZ");
            $this->assertStringContainsString('Barangay Cogon East', $xml, "Document '{$docName}' should contain Barangay Cogon East");

            @unlink($tempOutput);
            $docRequest->delete();
        }
    }
}
