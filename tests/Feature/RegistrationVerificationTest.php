<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationOtpMail;

class RegistrationVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'e_barangay',
        ]);
        Http::fake([
            'https://api.textbee.dev/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_registration_requires_age_and_birthplace()
    {
        $response = $this->post(route('resident.register.submit'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'birthdate' => '2000-01-15',
            // Missing age and birthplace
            'gender' => 'Male',
            'civil_status' => 'Single',
            'nationality' => 'Filipino',
            'occupation' => 'Worker',
            'purok' => 'Purok 1',
            'barangay_address' => 'Cogon East',
            'municipality' => 'Carmen, Cebu',
            'zip_code' => '6005',
            'phone_number' => '09991112233',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'id_type' => 'National ID',
            'proof_of_residency' => UploadedFile::fake()->image('id.jpg'),
            'terms' => '1',
            'privacy' => '1',
        ]);

        $response->assertSessionHasErrors(['age', 'birthplace']);
    }

    public function test_send_registration_otp_email_success()
    {
        Mail::fake();

        $email = 'otp.test@example.com';
        User::where('email', $email)->delete();
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $response = $this->postJson(route('resident.register.send_otp'), [
            'email' => $email,
            'phone_number' => '09991234567',
            'first_name' => 'Troy',
            'target_type' => 'email',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'target' => $email,
                     'target_type' => 'email',
                 ]);

        // Assert code stored in password_reset_tokens
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        $this->assertNotNull($record);
        $this->assertEquals(5, strlen($record->token));

        Mail::assertSent(RegistrationOtpMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        // Clean up
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    public function test_send_registration_otp_detects_duplicate_email()
    {
        // Existing user
        $existing = User::first();
        $this->assertNotNull($existing);

        $response = $this->postJson(route('resident.register.send_otp'), [
            'email' => $existing->email,
            'phone_number' => '09991234567',
            'target_type' => 'email',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'field' => 'email',
                 ]);
    }

    public function test_verify_registration_otp_validation()
    {
        $email = 'verify.test@example.com';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => '54321', 'created_at' => now()]
        );

        // Wrong code fails
        $wrongResponse = $this->withSession(['reg_verify_target' => $email])
                              ->postJson(route('resident.register.verify_otp'), [
                                  'code' => '99999',
                                  'target' => $email,
                              ]);

        $wrongResponse->assertStatus(422)
                      ->assertJson(['success' => false]);

        // Correct code succeeds
        $correctResponse = $this->withSession(['reg_verify_target' => $email])
                                ->postJson(route('resident.register.verify_otp'), [
                                    'code' => '54321',
                                    'target' => $email,
                                ]);

        $correctResponse->assertStatus(200)
                        ->assertJson([
                            'success' => true,
                            'verified_target' => $email,
                        ]);

        // Clean up
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    public function test_registration_requires_pre_verification_before_submit()
    {
        $email = 'unverified@example.com';
        $phone = '09880001122';

        User::where('email', $email)->orWhere('phone_number', $phone)->delete();

        // Submit WITHOUT pre-verification in session
        $response = $this->post(route('resident.register.submit'), [
            'first_name' => 'Unverified',
            'last_name' => 'User',
            'birthdate' => '1995-01-01',
            'age' => 31,
            'birthplace' => 'Carmen, Cebu',
            'gender' => 'Male',
            'civil_status' => 'Single',
            'nationality' => 'Filipino',
            'occupation' => 'Student',
            'purok' => 'Purok 1',
            'barangay_address' => 'Cogon East',
            'municipality' => 'Carmen, Cebu',
            'zip_code' => '6005',
            'phone_number' => $phone,
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'id_type' => 'National ID',
            'proof_of_residency' => UploadedFile::fake()->image('id.jpg'),
            'terms' => '1',
            'privacy' => '1',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registration_with_pre_verified_contact_succeeds_to_pending_approval()
    {
        $email = 'preverified@example.com';
        $phone = '09880003344';

        User::where('email', $email)->orWhere('phone_number', $phone)->delete();

        // Submit WITH pre-verification in session
        $response = $this->withSession(['reg_verified_target' => $email])
                         ->post(route('resident.register.submit'), [
                             'first_name' => 'Verified',
                             'last_name' => 'Resident',
                             'birthdate' => '1996-06-15',
                             'age' => 30,
                             'birthplace' => 'Carmen, Cebu',
                             'gender' => 'Female',
                             'civil_status' => 'Single',
                             'nationality' => 'Filipino',
                             'occupation' => 'Nurse',
                             'purok' => 'Purok 4',
                             'sitio' => 'Sitio Central',
                             'barangay_address' => 'Cogon East',
                             'municipality' => 'Carmen, Cebu',
                             'zip_code' => '6005',
                             'phone_number' => $phone,
                             'email' => $email,
                             'password' => 'Password123!',
                             'password_confirmation' => 'Password123!',
                             'id_type' => 'National ID',
                             'proof_of_residency' => UploadedFile::fake()->image('id.jpg'),
                             'terms' => '1',
                             'privacy' => '1',
                         ]);

        $response->assertRedirect(route('resident.pending') . '?notify=email');

        // Verify user in DB
        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertEquals(30, $user->age);
        $this->assertEquals('CARMEN, CEBU', $user->birthplace);
        $this->assertEquals('pending_approval', $user->status);
        $this->assertNotNull($user->email_verified_at);

        // Clean up
        $user->delete();
    }

    public function test_send_registration_otp_sms_success()
    {
        $phone = '09880007788';
        $email = 'sms.test@example.com';
        User::where('email', $email)->orWhere('phone_number', $phone)->delete();
        DB::table('password_reset_tokens')->where('email', $phone)->delete();

        $response = $this->postJson(route('resident.register.send_otp'), [
            'email' => $email,
            'phone_number' => $phone,
            'first_name' => 'Troy',
            'target_type' => 'phone',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'target' => $phone,
                     'target_type' => 'phone',
                 ]);

        // Assert code stored in password_reset_tokens
        $record = DB::table('password_reset_tokens')->where('email', $phone)->first();
        $this->assertNotNull($record);
        $this->assertEquals(5, strlen($record->token));

        // Clean up
        DB::table('password_reset_tokens')->where('email', $phone)->delete();
    }

    public function test_registration_with_sms_pre_verified_contact_succeeds_to_pending_approval()
    {
        $email = 'smspreverified@example.com';
        $phone = '09880005566';

        User::where('email', $email)->orWhere('phone_number', $phone)->delete();

        // Submit WITH pre-verification of PHONE in session when BOTH email and phone are provided
        $response = $this->withSession(['reg_verified_target' => $phone])
                         ->post(route('resident.register.submit'), [
                             'first_name' => 'Verified',
                             'last_name' => 'Resident',
                             'birthdate' => '1996-06-15',
                             'age' => 30,
                             'birthplace' => 'Carmen, Cebu',
                             'gender' => 'Female',
                             'civil_status' => 'Single',
                             'nationality' => 'Filipino',
                             'occupation' => 'Nurse',
                             'purok' => 'Purok 4',
                             'sitio' => 'Sitio Central',
                             'barangay_address' => 'Cogon East',
                             'municipality' => 'Carmen, Cebu',
                             'zip_code' => '6005',
                             'phone_number' => $phone,
                             'email' => $email,
                             'password' => 'Password123!',
                             'password_confirmation' => 'Password123!',
                             'id_type' => 'National ID',
                             'proof_of_residency' => UploadedFile::fake()->image('id.jpg'),
                             'terms' => '1',
                             'privacy' => '1',
                         ]);

        $response->assertRedirect(route('resident.pending') . '?notify=email');

        // Verify user in DB
        $user = User::where('phone_number', $phone)->first();
        $this->assertNotNull($user);
        $this->assertEquals(30, $user->age);
        $this->assertEquals('pending_approval', $user->status);
        $this->assertNotNull($user->email_verified_at);

        // Clean up
        $user->delete();
    }

    public function test_rejected_account_can_request_otp_and_re_register_with_same_email_and_phone()
    {
        Mail::fake();
        \Illuminate\Support\Facades\Storage::fake('public');

        $email = 'rejected_reregister_' . uniqid() . '@example.com';
        $phone = '0977' . rand(1000000, 9999999);

        User::where('email', $email)->orWhere('phone_number', $phone)->delete();
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $oldIdPath = 'residency_proofs/old_rejected_id_' . uniqid() . '.jpg';
        $oldSelfiePath = 'selfies/old_rejected_selfie_' . uniqid() . '.jpg';
        \Illuminate\Support\Facades\Storage::disk('public')->put($oldIdPath, 'old-id');
        \Illuminate\Support\Facades\Storage::disk('public')->put($oldSelfiePath, 'old-selfie');

        $rejectedUser = User::create([
            'user_id'            => 'USR-REJ' . rand(100, 999),
            'name'               => 'OLD REJECTED NAME',
            'first_name'         => 'Old',
            'last_name'          => 'Rejected',
            'email'              => $email,
            'phone_number'       => $phone,
            'password'           => bcrypt('OldPass123!'),
            'role'               => 'Resident',
            'status'             => 'rejected',
            'proof_of_residency' => $oldIdPath,
            'selfie_photo'       => $oldSelfiePath,
        ]);

        try {
            // Step 1: Rejected email & mobile should NOT be blocked from requesting OTP
            $otpResponse = $this->postJson(route('resident.register.send_otp'), [
                'email'        => $email,
                'phone_number' => $phone,
                'first_name'   => 'Troy',
                'target_type'  => 'email',
            ]);

            $otpResponse->assertStatus(200)
                ->assertJson([
                    'success'     => true,
                    'target'      => $email,
                    'target_type' => 'email',
                ]);

            Mail::assertSent(RegistrationOtpMail::class, fn ($mail) => $mail->hasTo($email));

            // Step 2: Re-register with updated details, new ID, and new selfie
            $registerResponse = $this->withSession(['reg_verified_target' => $email])
                ->post(route('resident.register.submit'), [
                    'first_name'            => 'Troy',
                    'last_name'             => 'Suson',
                    'birthdate'             => '2003-05-10',
                    'age'                   => 23,
                    'birthplace'            => 'Carmen, Cebu',
                    'gender'                => 'Male',
                    'civil_status'          => 'Single',
                    'nationality'           => 'Filipino',
                    'occupation'            => 'Student',
                    'purok'                 => 'Purok 2',
                    'sitio'                 => 'Sitio Proper',
                    'barangay_address'      => 'Cogon East',
                    'municipality'          => 'Carmen, Cebu',
                    'zip_code'              => '6005',
                    'phone_number'          => $phone,
                    'email'                 => $email,
                    'password'              => 'NewPassword123!',
                    'password_confirmation' => 'NewPassword123!',
                    'id_type'               => 'National ID',
                    'proof_of_residency'    => UploadedFile::fake()->image('new_id.jpg'),
                    'selfie_upload'         => UploadedFile::fake()->image('new_selfie.jpg'),
                    'terms'                 => '1',
                    'privacy'               => '1',
                ]);

            $registerResponse->assertRedirect(route('resident.pending') . '?notify=email');

            $rejectedUser->refresh();
            $this->assertSame('pending_approval', $rejectedUser->status);
            $this->assertSame('TROY SUSON', $rejectedUser->name);
            $this->assertSame('National ID', $rejectedUser->id_type);
            $this->assertNotEquals($oldIdPath, $rejectedUser->proof_of_residency);
            $this->assertNotEquals($oldSelfiePath, $rejectedUser->selfie_photo);
            $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewPassword123!', $rejectedUser->password));
            $this->assertSame(1, User::where('email', $email)->count());

            \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($oldIdPath);
            \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($oldSelfiePath);
            \Illuminate\Support\Facades\Storage::disk('public')->assertExists($rejectedUser->proof_of_residency);
            \Illuminate\Support\Facades\Storage::disk('public')->assertExists($rejectedUser->selfie_photo);
        } finally {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            User::where('id', $rejectedUser->id)->delete();
        }
    }
}

