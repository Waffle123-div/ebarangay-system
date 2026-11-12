<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminIdDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_id_document_url_uses_admin_route_and_preserves_file_extension(): void
    {
        $residentWithProof = User::create([
            'user_id' => 'RES-DOC1',
            'name' => 'Proof Resident',
            'email' => 'proof.resident@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'proof_of_residency' => 'residency_proofs/national_id.jpg',
        ]);

        $residentWithPdf = User::create([
            'user_id' => 'RES-DOC2',
            'name' => 'PDF Resident',
            'email' => 'pdf.resident@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'id_photo' => 'valid_ids/passport_scan.pdf',
        ]);

        $residentWithUrl = User::create([
            'user_id' => 'RES-DOC3',
            'name' => 'URL Resident',
            'email' => 'url.resident@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'proof_of_residency' => 'https://cdn.example.com/ids/sample.png',
        ]);

        $this->assertSame(
            route('admin.resident.id_document', ['user' => $residentWithProof->id, 'filename' => 'national_id.jpg']),
            $residentWithProof->id_document_url
        );
        $this->assertSame(
            route('admin.resident.id_document', ['user' => $residentWithPdf->id, 'filename' => 'passport_scan.pdf']),
            $residentWithPdf->id_document_url
        );
        $this->assertSame(
            'https://cdn.example.com/ids/sample.png',
            $residentWithUrl->id_document_url
        );
    }

    public function test_guest_and_non_admin_cannot_access_resident_id_document(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('residency_proofs/secret_id.png', 'fake-image-bytes');

        $resident = User::create([
            'user_id' => 'RES-DOC4',
            'name' => 'Target Resident',
            'email' => 'target.resident@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'proof_of_residency' => 'residency_proofs/secret_id.png',
        ]);

        $otherResident = User::create([
            'user_id' => 'RES-DOC5',
            'name' => 'Other Resident',
            'email' => 'other.resident@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'active',
        ]);

        $url = route('admin.resident.id_document', ['user' => $resident->id, 'filename' => 'secret_id.png']);

        $this->get($url)->assertRedirect();

        $this->actingAs($otherResident)
            ->get($url)
            ->assertRedirect(route('resident.dashboard'));
    }

    public function test_admin_can_stream_resident_id_document_from_public_and_local_disks_without_symlink(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        Storage::disk('public')->put('residency_proofs/front_id.png', "\x89PNG\r\n\x1a\nfake-png");
        Storage::disk('local')->put('valid_ids/clearance.pdf', '%PDF-1.4 fake-pdf');

        $admin = User::create([
            'user_id' => 'ADM-DOC1',
            'name' => 'Admin Reviewer',
            'email' => 'admin.reviewer@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('SecretPass123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $publicResident = User::create([
            'user_id' => 'RES-DOC6',
            'name' => 'Public Disk Resident',
            'email' => 'public.disk@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'proof_of_residency' => 'residency_proofs/front_id.png',
        ]);

        $localResident = User::create([
            'user_id' => 'RES-DOC7',
            'name' => 'Local Disk Resident',
            'email' => 'local.disk@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'id_photo' => 'valid_ids/clearance.pdf',
        ]);

        $missingResident = User::create([
            'user_id' => 'RES-DOC8',
            'name' => 'Missing File Resident',
            'email' => 'missing.file@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'proof_of_residency' => 'residency_proofs/nonexistent.jpg',
        ]);

        $publicResponse = $this->actingAs($admin)->get($publicResident->id_document_url);
        $publicResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $publicResponse->headers->get('Content-Disposition'));

        $localResponse = $this->actingAs($admin)->get($localResident->id_document_url);
        $localResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $localResponse->headers->get('Content-Disposition'));

        $this->actingAs($admin)
            ->get($missingResident->id_document_url)
            ->assertNotFound();
    }

    public function test_admin_account_approvals_page_renders_authenticated_id_document_route(): void
    {
        $admin = User::create([
            'user_id' => 'ADM-DOC2',
            'name' => 'Approvals Admin',
            'email' => 'approvals.admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('SecretPass123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $pendingResident = User::create([
            'user_id' => 'RES-DOC9',
            'name' => 'Pending Approval Resident',
            'email' => 'pending.resident@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => 'resident',
            'status' => 'pending_approval',
            'proof_of_residency' => 'residency_proofs/pending_id.jpg',
        ]);

        $expectedUrl = route('admin.resident.id_document', [
            'user' => $pendingResident->id,
            'filename' => 'pending_id.jpg',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.approvals'));
        $response->assertOk();
        $response->assertSee('data-doc-url="' . $expectedUrl . '"', false);
    }
}

