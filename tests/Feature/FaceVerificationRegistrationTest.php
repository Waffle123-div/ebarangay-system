<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FaceVerificationRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);
        Storage::fake('public');
    }

    public function test_registration_with_base64_face_selfie_stores_image_and_sets_timestamp()
    {
        $email = 'face_resident_' . uniqid() . '@example.com';

        // Pre-verify in session
        $this->withSession([
            'reg_verified_target' => $email,
            'reg_verified_at'     => now(),
        ]);

        // 1x1 transparent GIF or JPEG base64
        $fakeBase64 = 'data:image/jpeg;base64,' . base64_encode(imagecreate(10, 10) ? "\xFF\xD8\xFF\xE0\x00\x10JFIF" : 'fake-image-data');

        $response = $this->post(route('resident.register.submit'), [
            'first_name'         => 'Juan',
            'last_name'          => 'Dela Cruz',
            'middle_name'        => 'Santos',
            'suffix'             => '',
            'birthdate'          => '1995-05-15',
            'age'                => 31,
            'birthplace'         => 'Cebu City',
            'gender'             => 'Male',
            'civil_status'       => 'Single',
            'nationality'        => 'Filipino',
            'occupation'         => 'Software Developer',
            'purok'              => 'Purok 1',
            'sitio'              => '',
            'barangay_address'   => 'Barangay Central',
            'municipality'       => 'Cebu City',
            'zip_code'           => '6000',
            'email'              => $email,
            'phone_number'       => '0917' . rand(1000000, 9999999),
            'password'           => 'Password123!',
            'password_confirmation' => 'Password123!',
            'id_type'            => 'Philippine Passport',
            'proof_of_residency' => UploadedFile::fake()->image('passport.jpg'),
            'selfie_photo'       => $fakeBase64,
            'terms'              => '1',
            'privacy'            => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'User should be saved in database');
        $this->assertNotNull($user->selfie_photo, 'selfie_photo column should be populated');
        $this->assertNotNull($user->face_verified_at, 'face_verified_at timestamp should be set');
        $this->assertTrue(Storage::disk('public')->exists($user->selfie_photo), 'Selfie image file should be stored on public disk');
        $this->assertNotNull($user->selfie_photo_url, 'selfie_photo_url accessor should return valid URL');
    }

    public function test_registration_with_uploaded_selfie_fallback_stores_image_and_sets_timestamp()
    {
        $email = 'fallback_resident_' . uniqid() . '@example.com';

        // Pre-verify in session
        $this->withSession([
            'reg_verified_target' => $email,
            'reg_verified_at'     => now(),
        ]);

        $response = $this->post(route('resident.register.submit'), [
            'first_name'         => 'Maria',
            'last_name'          => 'Santos',
            'middle_name'        => 'Reyes',
            'suffix'             => '',
            'birthdate'          => '1998-08-20',
            'age'                => 28,
            'birthplace'         => 'Davao City',
            'gender'             => 'Female',
            'civil_status'       => 'Single',
            'nationality'        => 'Filipino',
            'occupation'         => 'Teacher',
            'purok'              => 'Purok 4',
            'sitio'              => '',
            'barangay_address'   => 'Barangay Central',
            'municipality'       => 'Davao City',
            'zip_code'           => '8000',
            'email'              => $email,
            'phone_number'       => '0918' . rand(1000000, 9999999),
            'password'           => 'Password123!',
            'password_confirmation' => 'Password123!',
            'id_type'            => 'Driver License',
            'proof_of_residency' => UploadedFile::fake()->image('license.jpg'),
            'selfie_upload'      => UploadedFile::fake()->image('selfie_camera_fallback.jpg'),
            'terms'              => '1',
            'privacy'            => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user, 'User should be saved in database');
        $this->assertNotNull($user->selfie_photo, 'selfie_photo should be stored');
        $this->assertNotNull($user->face_verified_at, 'face_verified_at timestamp should be set');
        $this->assertTrue(Storage::disk('public')->exists($user->selfie_photo), 'Uploaded selfie file should be stored on public disk');
    }

    public function test_admin_approvals_modal_includes_side_by_side_face_verification_elements()
    {
        $admin = User::where('role', 'Admin')->first();
        if (!$admin) {
            $admin = User::create([
                'user_id' => 'USR-ADMIN-' . uniqid(),
                'name' => 'Admin Face Reviewer',
                'email' => 'admin_face_review@example.com',
                'password' => bcrypt('admin123'),
                'role' => 'Admin',
                'status' => 'active',
            ]);
        }

        $resident = User::create([
            'user_id' => 'USR-' . rand(10000, 99999),
            'name' => 'Face Verified Resident',
            'email' => 'face_resident_approvals_' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'Resident',
            'status' => 'pending_approval',
            'id_type' => 'National ID',
            'proof_of_residency' => 'residency_proofs/sample_id.jpg',
            'selfie_photo' => 'selfies/sample_selfie.jpg',
            'face_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.approvals'));

        $response->assertStatus(200);
        $response->assertSee('Account Approvals');
        $response->assertSee('View ID & Face', false);
        $response->assertSee('selfiePreviewWrapper', false);
        $response->assertSee('docPreviewImgWrapper', false);
        $response->assertSee('Government-Issued ID', false);
        $response->assertSee('Live Facial Selfie', false);
    }

    public function test_admin_can_stream_selfie_from_public_disk()
    {
        Storage::fake('public');
        Storage::disk('public')->put('selfies/test_stream_public.jpg', 'fake-image-content');

        $admin = User::where('role', 'Admin')->first() ?? User::create([
            'user_id' => 'USR-ADMIN-' . uniqid(),
            'name' => 'Admin Selfie Streamer',
            'email' => 'admin_streamer_' . uniqid() . '@example.com',
            'password' => bcrypt('admin123'),
            'role' => 'Admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.resident.selfie', 'test_stream_public.jpg'));
        $response->assertStatus(200);
    }

    public function test_admin_can_stream_selfie_from_local_disk()
    {
        Storage::fake('local');
        Storage::disk('local')->put('selfies/test_stream_local.jpg', 'fake-local-image-content');

        $admin = User::where('role', 'Admin')->first() ?? User::create([
            'user_id' => 'USR-ADMIN-' . uniqid(),
            'name' => 'Admin Selfie Streamer Local',
            'email' => 'admin_streamer_local_' . uniqid() . '@example.com',
            'password' => bcrypt('admin123'),
            'role' => 'Admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.resident.selfie', 'test_stream_local.jpg'));
        $response->assertStatus(200);
    }

    public function test_unauthorized_user_cannot_stream_resident_selfie()
    {
        Storage::fake('public');
        Storage::disk('public')->put('selfies/secret_selfie.jpg', 'secret-image-content');

        $response = $this->get(route('admin.resident.selfie', 'secret_selfie.jpg'));
        $response->assertRedirect();
    }
}
