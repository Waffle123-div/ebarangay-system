<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentType;
use App\Models\DocumentRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class DocumentTypeForeignKeyTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);
    }

    public function test_document_requests_table_has_document_type_id_foreign_key(): void
    {
        // Verify column exists in schema
        $column = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'document_requests'
              AND COLUMN_NAME = 'document_type_id'
        ");

        $this->assertNotEmpty($column, 'document_type_id column must exist in document_requests table');
        $this->assertEquals('bigint', strtolower($column[0]->DATA_TYPE));
        $this->assertStringContainsString('unsigned', strtolower($column[0]->COLUMN_TYPE));

        // Verify foreign key constraint linking to document_types.id
        $fk = DB::select("
            SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'document_requests'
              AND COLUMN_NAME = 'document_type_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        $this->assertNotEmpty($fk, 'Foreign key constraint must exist on document_type_id');
        $this->assertEquals('document_types', $fk[0]->REFERENCED_TABLE_NAME);
        $this->assertEquals('id', $fk[0]->REFERENCED_COLUMN_NAME);
    }

    public function test_document_request_syncs_with_document_type_relationship(): void
    {
        $uniqueName = 'FK Test Certificate ' . uniqid();
        $docType = DocumentType::create([
            'name'            => $uniqueName,
            'fee'             => 50.00,
            'processing_days' => '1-2 Days',
            'is_active'       => true,
        ]);

        $request = DocumentRequest::create([
            'tracking_number' => 'TRK-' . uniqid(),
            'document_type'   => $uniqueName,
            'status'          => 'Pending',
            'first_name'      => 'Juan',
            'last_name'       => 'Dela Cruz',
            'resident_email'  => 'resident.fk@example.com',
        ]);

        // Model booted saving hook should automatically link document_type_id
        $this->assertEquals($docType->id, $request->document_type_id);
        $this->assertInstanceOf(DocumentType::class, $request->documentType);
        $this->assertEquals($docType->id, $request->documentType->id);

        // Reverse relationship
        $this->assertTrue($docType->documentRequests->contains('id', $request->id));

        // When document type is deleted, foreign key sets null safely without crashing
        $docType->delete();
        $request->refresh();
        $this->assertNull($request->document_type_id);
        $this->assertEquals($uniqueName, $request->document_type); // Original historical string preserved
    }
}
