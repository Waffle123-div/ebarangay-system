<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentType;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DocumentManagementTemplateTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);

        $this->admin = User::create([
            'user_id'           => 'ADM-' . strtoupper(substr(uniqid(), -6)),
            'name'              => 'Admin Officer DocMan',
            'first_name'        => 'Admin',
            'last_name'         => 'Officer',
            'email'             => 'admin_docman_' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password123'),
            'role'              => 'Admin',
            'status'            => 'active',
        ]);
    }

    public function test_admin_can_view_document_management_with_word_template_column(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.document-types.index'));

        $response->assertStatus(200);
        $response->assertSee('WORD TEMPLATE');
        $response->assertSee('01_Barangay_Clearance_General.docx');
        $response->assertSee('02_Barangay_Business_Permit.docx');
        $response->assertSee('03_Electric_Connection_Cert.docx');
        $response->assertSee('04_Acknowledgement_Receipt.docx');
        $response->assertSee('05_Senior_Citizen_Cert.docx');
        $response->assertSee('06_Senior_Cash_Assistance.docx');
        $response->assertSee('07_Senior_Burial_Assistance.docx');
        $response->assertSee('08_Certificate_of_Cohabitation.docx');
        $response->assertSee('10_Probation_Bail_Brgy_Cert.docx');
        $response->assertSee('11_General_Barangay_Certificate.docx');
        $response->assertSee('12_First_Time_Jobseeker_Cert.docx');
        $response->assertSee('13_Oath_of_Undertaking_Jobseeker.docx');
        $response->assertSee('indigency_template.docx');
    }

    public function test_admin_can_download_document_type_template(): void
    {
        $clearanceDoc = DocumentType::where('name', 'Barangay Clearance')->first();
        $this->assertNotNull($clearanceDoc);

        $response = $this->actingAs($this->admin)->get(route('admin.document-types.download-template', $clearanceDoc->id));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');
    }

    public function test_template_tags_are_bold_in_document_templates(): void
    {
        $templates = [
            '01_Barangay_Clearance_General.docx',
            '02_Barangay_Business_Permit.docx',
            '03_Electric_Connection_Cert.docx',
            '04_Acknowledgement_Receipt.docx',
            '05_Senior_Citizen_Cert.docx',
            '06_Senior_Cash_Assistance.docx',
            '07_Senior_Burial_Assistance.docx',
            '08_Certificate_of_Cohabitation.docx',
            '10_Probation_Bail_Brgy_Cert.docx',
            '11_General_Barangay_Certificate.docx',
            '12_First_Time_Jobseeker_Cert.docx',
            '13_Oath_of_Undertaking_Jobseeker.docx',
            'indigency_template.docx',
            'residency_template.docx',
        ];

        foreach ($templates as $tmpl) {
            $path = storage_path('app/document_templates/' . $tmpl);
            $this->assertFileExists($path);

            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($path));
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();

            // Verify ${name} is enclosed in a run with bold styling (<w:b/>)
            $this->assertMatchesRegularExpression('/<w:r\b[^>]*>(?:(?!<\/w:r>).)*<w:b\b(?:(?!<\/w:r>).)*<w:t[^>]*>\$\{name\}<\/w:t>/s', $xml, "Tag \${name} should be bold in $tmpl");
        }
    }
}

