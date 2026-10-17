<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barangay_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('general')->index();
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            // Barangay Information & Identity
            ['key' => 'barangay_name', 'value' => 'Barangay San Antonio', 'group' => 'barangay'],
            ['key' => 'municipality', 'value' => 'City of Pasig', 'group' => 'barangay'],
            ['key' => 'province', 'value' => 'Metro Manila', 'group' => 'barangay'],
            ['key' => 'region', 'value' => 'National Capital Region (NCR)', 'group' => 'barangay'],
            ['key' => 'barangay_tagline', 'value' => 'Serbisyong Maaasahan, Tapat, at Makatao', 'group' => 'barangay'],
            ['key' => 'barangay_logo', 'value' => null, 'group' => 'barangay'],
            ['key' => 'municipal_logo', 'value' => null, 'group' => 'barangay'],

            // Officials & Signatories
            ['key' => 'barangay_captain', 'value' => 'Hon. Raymond C. Lising', 'group' => 'officials'],
            ['key' => 'barangay_secretary', 'value' => 'Maria Elena D. Santos', 'group' => 'officials'],
            ['key' => 'barangay_treasurer', 'value' => 'Roberto V. Ramos', 'group' => 'officials'],

            // Contact & Office Hours
            ['key' => 'barangay_hall_address', 'value' => '77 General Malvar St., San Antonio, Pasig City', 'group' => 'contact'],
            ['key' => 'official_contact', 'value' => '(02) 8631-0000 / 0917-123-4567', 'group' => 'contact'],
            ['key' => 'emergency_hotline', 'value' => '(02) 8643-0000 / 911', 'group' => 'contact'],
            ['key' => 'official_email', 'value' => 'sanantonio.pasig@gmail.com', 'group' => 'contact'],
            ['key' => 'office_hours', 'value' => 'Monday – Friday: 8:00 AM – 5:00 PM', 'group' => 'contact'],

            // Payment & Fees
            ['key' => 'payment_gcash_enabled', 'value' => '1', 'group' => 'payment'],
            ['key' => 'gcash_account_name', 'value' => 'Barangay San Antonio Treasury', 'group' => 'payment'],
            ['key' => 'gcash_account_number', 'value' => '0917-987-6543', 'group' => 'payment'],
            ['key' => 'gcash_qr_code', 'value' => null, 'group' => 'payment'],
            ['key' => 'payment_maya_enabled', 'value' => '0', 'group' => 'payment'],
            ['key' => 'maya_account_name', 'value' => 'Barangay San Antonio', 'group' => 'payment'],
            ['key' => 'maya_account_number', 'value' => '0917-987-6543', 'group' => 'payment'],
            ['key' => 'payment_instructions', 'value' => 'Scan the QR code or send to the number above. Please make sure to save a screenshot of your payment receipt and reference number.', 'group' => 'payment'],

            // Document Processing Rules
            ['key' => 'default_processing_days', 'value' => '2', 'group' => 'documents'],
            ['key' => 'tracking_prefix', 'value' => 'BRGY-DOC', 'group' => 'documents'],
            ['key' => 'online_requests_enabled', 'value' => '1', 'group' => 'documents'],
            ['key' => 'max_active_requests_per_resident', 'value' => '3', 'group' => 'documents'],

            // Resident & Registration Rules
            ['key' => 'resident_verification_mode', 'value' => 'manual', 'group' => 'residents'],
            ['key' => 'minimum_registration_age', 'value' => '15', 'group' => 'residents'],
            ['key' => 'purok_list', 'value' => "Purok 1\nPurok 2\nPurok 3\nPurok 4\nPurok 5\nSitio Ilaya\nSitio Ibaba\nGreenwoods Village", 'group' => 'residents'],
            ['key' => 'accepted_ids', 'value' => "Philippine Passport\nPhilSys National ID\nDriver's License\nUMID / SSS ID\nVoter's ID / Certification\nPostal ID\nPRC ID\nStudent ID (with Enrollment Form)", 'group' => 'residents'],

            // Portal Announcements
            ['key' => 'announcement_enabled', 'value' => '0', 'group' => 'announcements'],
            ['key' => 'announcement_type', 'value' => 'info', 'group' => 'announcements'],
            ['key' => 'announcement_title', 'value' => 'Public Advisory', 'group' => 'announcements'],
            ['key' => 'announcement_message', 'value' => 'Welcome to the official E-Barangay Online Portal. You may request certificates and clearances online 24/7.', 'group' => 'announcements'],

            // System & Maintenance
            ['key' => 'system_maintenance_mode', 'value' => '0', 'group' => 'system'],
            ['key' => 'session_timeout_minutes', 'value' => '120', 'group' => 'system'],
        ];

        foreach ($defaults as &$record) {
            $record['created_at'] = $now;
            $record['updated_at'] = $now;
        }

        DB::table('barangay_settings')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangay_settings');
    }
};
