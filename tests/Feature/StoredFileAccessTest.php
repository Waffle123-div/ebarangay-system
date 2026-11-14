<?php

namespace Tests\Feature;

use App\Models\BarangaySetting;
use App\Models\CommunityConcern;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Services\StoredFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoredFileAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'user_id' => 'ADM-ATT1',
            'name' => 'Admin Staff',
            'email' => 'admin.staff@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('SecretPass123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    private function createVerifiedResident(string $userId, string $email, array $overrides = []): User
    {
        return User::create(array_merge([
            'user_id' => $userId,
            'name' => 'Test Resident',
            'email' => $email,
            'email_verified_at' => now(),
            'security_pin' => bcrypt('123456'),
            'password' => bcrypt('SecretPass123!'),
            'role' => 'Resident',
            'status' => 'active',
        ], $overrides));
    }

    public function test_admin_can_preview_and_download_request_attachments_and_profile_proofs_without_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('attachments/utility_bill.pdf', '%PDF-1.4 utility bill');
        Storage::disk('public')->put('residency_proofs/proof_residency.png', "\x89PNG\r\n\x1a\nfake-png");

        $admin = $this->createAdmin();
        $resident = $this->createVerifiedResident('RES-ATT1', 'res.att1@example.com', [
            'proof_of_residency' => 'residency_proofs/proof_residency.png',
        ]);

        $docRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'tracking_number' => 'REQ-TEST-1001',
            'last_name' => 'Resident',
            'first_name' => 'Test',
            'contact_number' => '09171234567',
            'resident_email' => $resident->email,
            'purok' => 'Purok 1',
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Employment',
            'fee' => 0,
            'status' => 'Pending',
            'attachments' => [
                [
                    'file_path' => 'attachments/utility_bill.pdf',
                    'file_name' => 'utility_bill.pdf',
                    'doc_type' => 'Utility Bill',
                    'file_type' => 'document',
                    'file_size' => 2048,
                ],
            ],
        ]);

        $inlineUrl = route('admin.requests.attachment', [
            'documentRequest' => $docRequest->id,
            'index' => 0,
            'filename' => 'utility_bill.pdf',
        ]);
        $downloadUrl = route('admin.requests.attachment', [
            'documentRequest' => $docRequest->id,
            'index' => 0,
            'filename' => 'utility_bill.pdf',
            'download' => 1,
        ]);
        $profileProofUrl = route('admin.requests.attachment', [
            'documentRequest' => $docRequest->id,
            'index' => 1,
            'filename' => 'proof_residency.png',
        ]);

        // Unauthenticated and non-admin access are blocked
        $this->get($inlineUrl)->assertRedirect();
        $this->actingAs($resident)->get($inlineUrl)->assertRedirect(route('resident.dashboard'));

        // Admin inline preview works for both request attachment and profile proof_of_residency
        $inlineResponse = $this->actingAs($admin)->get($inlineUrl);
        $inlineResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $inlineResponse->headers->get('Content-Disposition'));

        $proofResponse = $this->actingAs($admin)->get($profileProofUrl);
        $proofResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $proofResponse->headers->get('Content-Disposition'));

        // Admin download sets attachment disposition
        $downloadResponse = $this->actingAs($admin)->get($downloadUrl);
        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment', (string) $downloadResponse->headers->get('Content-Disposition'));

        // Admin requests page renders authenticated route URLs instead of /storage/...
        $requestsPage = $this->actingAs($admin)->get(route('admin.requests'));
        $requestsPage->assertOk();
        $requestsPage->assertSee($inlineUrl, false);
        $requestsPage->assertSee($profileProofUrl, false);

        // Out-of-bounds index returns 404
        $this->actingAs($admin)
            ->get(route('admin.requests.attachment', ['documentRequest' => $docRequest->id, 'index' => 99]))
            ->assertNotFound();
    }

    public function test_concern_photo_routes_enforce_access_and_stream_without_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('concerns/streetlight.jpg', 'fake-jpg-bytes');

        $admin = $this->createAdmin();
        $owner = $this->createVerifiedResident('RES-CON1', 'owner.concern@example.com');
        $otherResident = $this->createVerifiedResident('RES-CON2', 'other.concern@example.com');

        $concern = CommunityConcern::create([
            'concern_id' => 'CON-2026-00001',
            'user_id' => $owner->id,
            'resident_name' => $owner->name,
            'resident_email' => $owner->email,
            'contact_number' => '09171234567',
            'address' => 'Purok 1',
            'title' => 'Broken Streetlight',
            'category' => 'Street Lighting',
            'description' => 'Lamp post is not turning on at night.',
            'location' => 'Main Road',
            'incident_date' => now()->toDateString(),
            'photo_path' => 'concerns/streetlight.jpg',
            'status' => 'Pending',
        ]);

        $adminConcernPhotoUrl = route('admin.concerns.photo', ['concern' => $concern->id, 'filename' => 'streetlight.jpg']);
        $residentConcernPhotoUrl = route('resident.concerns.photo', ['concern' => $concern->id, 'filename' => 'streetlight.jpg']);

        $this->actingAs($admin)->get($adminConcernPhotoUrl)->assertOk();
        $this->actingAs($owner)->get($residentConcernPhotoUrl)->assertOk();
        $this->actingAs($otherResident)->get($residentConcernPhotoUrl)->assertForbidden();

        $this->actingAs($admin)->get(route('admin.concerns'))->assertOk()->assertSee($adminConcernPhotoUrl, false);
        $this->actingAs($owner)->get(route('resident.concerns'))->assertOk()->assertSee($residentConcernPhotoUrl, false);
    }

    public function test_resident_settings_file_route_streams_profile_and_id_photo_without_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('profile_pictures/avatar.png', 'fake-avatar-bytes');
        Storage::disk('public')->put('valid_ids/national_id.jpg', 'fake-id-bytes');

        $resident = $this->createVerifiedResident('RES-SET1', 'settings.resident@example.com', [
            'profile_picture' => 'profile_pictures/avatar.png',
            'id_photo' => 'valid_ids/national_id.jpg',
        ]);

        $avatarUrl = route('resident.settings.file', ['type' => 'profile_picture', 'filename' => 'avatar.png']);
        $idUrl = route('resident.settings.file', ['type' => 'id_photo', 'filename' => 'national_id.jpg']);

        $this->get($avatarUrl)->assertRedirect();
        $this->actingAs($resident)->get($avatarUrl)->assertOk();
        $this->actingAs($resident)->get($idUrl)->assertOk();

        $settingsPage = $this->actingAs($resident)->get(route('resident.settings'));
        $settingsPage->assertOk();
        $settingsPage->assertSee($avatarUrl, false);
        $settingsPage->assertSee($idUrl, false);
    }

    public function test_cloud_s3_uploads_branding_and_avatars_stream_through_app_routes(): void
    {
        Storage::fake('s3');
        Storage::fake('public');

        config([
            'filesystems.default' => 's3',
            'filesystems.disks.s3.bucket' => 'laravel-cloud-private-bucket',
        ]);

        $this->assertTrue(StoredFileService::usesCloudStorage());
        $this->assertSame('s3', StoredFileService::uploadDisk('public'));

        $admin = $this->createAdmin();
        $resident = $this->createVerifiedResident('RES-CLOUD1', 'cloud.resident@example.com');

        // 1. Admin uploads barangay logo & municipal logo under S3 config
        $logoFile = UploadedFile::fake()->image('barangay_seal.png', 100, 100);
        $municipalFile = UploadedFile::fake()->image('municipal_seal.png', 100, 100);

        $this->actingAs($admin)->post(route('admin.settings.barangay'), [
            '_group' => 'barangay',
            'barangay_name' => 'Barangay Cogon East',
            'municipality' => 'Ormoc City',
            'province' => 'Leyte',
            'barangay_logo' => $logoFile,
            'municipal_logo' => $municipalFile,
        ])->assertRedirect();

        $savedLogoPath = BarangaySetting::get('barangay_logo');
        $savedMunicipalPath = BarangaySetting::get('municipal_logo');

        $this->assertNotEmpty($savedLogoPath);
        $this->assertNotEmpty($savedMunicipalPath);
        Storage::disk('s3')->assertExists($savedLogoPath);
        Storage::disk('s3')->assertExists($savedMunicipalPath);
        Storage::disk('public')->assertMissing($savedLogoPath);

        $expectedLogoUrl = route('branding.asset', [
            'key' => 'barangay_logo',
            'filename' => basename($savedLogoPath),
        ]);
        $this->get($expectedLogoUrl)->assertOk();

        $adminSettingsPage = $this->actingAs($admin)->get(route('admin.settings'));
        $adminSettingsPage->assertOk();
        $adminSettingsPage->assertSee($expectedLogoUrl, false);
        $adminSettingsPage->assertDontSee('/storage/' . $savedLogoPath, false);

        // 2. Resident uploads profile picture to S3 and streams it via media.profile_avatar & resident.settings.file
        $residentAvatar = UploadedFile::fake()->image('resident_avatar.jpg', 120, 120);
        $this->actingAs($resident)->post(route('resident.settings.profile'), [
            'first_name' => 'Test',
            'last_name' => 'Resident',
            'birthdate' => '1998-01-15',
            'age' => 28,
            'birthplace' => 'Ormoc City',
            'gender' => 'Male',
            'civil_status' => 'Single',
            'nationality' => 'Filipino',
            'occupation' => 'Engineer',
            'phone_number' => '09171234567',
            'house_number' => '12',
            'street' => 'Rizal St',
            'barangay_address' => 'Cogon East',
            'municipality' => 'Ormoc City',
            'province' => 'Leyte',
            'zip_code' => '6541',
            'profile_picture' => $residentAvatar,
        ])->assertRedirect();

        $resident->refresh();
        $this->assertNotNull($resident->profile_picture);
        Storage::disk('s3')->assertExists($resident->profile_picture);
        Storage::disk('public')->assertMissing($resident->profile_picture);

        $this->actingAs($resident)->get($resident->profile_picture_url)->assertOk();

        // 3. Admin avatar stored on S3 streams via admin.profile.avatar and appears on admin profile page
        Storage::disk('s3')->put('profile_pictures/admin_cloud.png', 'cloud-admin-avatar-bytes');
        $admin->update(['profile_picture' => 'profile_pictures/admin_cloud.png']);

        $this->actingAs($admin)->get($admin->profile_picture_url)->assertOk();
        $this->actingAs($admin)->get(route('admin.profile'))
            ->assertOk()
            ->assertSee($admin->profile_picture_url, false);

        // 4. Deleting stored files cleans up S3 object cleanly
        StoredFileService::delete($admin->profile_picture, ['profile_pictures']);
        Storage::disk('s3')->assertMissing('profile_pictures/admin_cloud.png');

        // 5. Private ID document and selfie on S3 stream via admin routes
        Storage::disk('s3')->put('residency_proofs/cloud_id.png', "\x89PNG\r\n\x1a\ncloud-id");
        Storage::disk('s3')->put('selfies/cloud_selfie.jpg', 'cloud-selfie');
        $resident->update([
            'proof_of_residency' => 'residency_proofs/cloud_id.png',
            'selfie_photo' => 'selfies/cloud_selfie.jpg',
        ]);

        $this->actingAs($admin)->get($resident->id_document_url)->assertOk();
        $this->actingAs($admin)->get($resident->selfie_photo_url)->assertOk();

        // 6. Custom disk names injected by Laravel Cloud (e.g. 'r2') are auto-detected
        config([
            'filesystems.default' => 'r2',
            'filesystems.disks.r2' => [
                'driver' => 's3',
                'bucket' => 'laravel-cloud-r2-bucket',
            ],
        ]);
        $this->assertSame('r2', StoredFileService::cloudDisk());
        $this->assertSame('r2', StoredFileService::uploadDisk('public'));
    }
}

