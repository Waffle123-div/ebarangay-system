<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds all columns required for the Resident Profile Settings module.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // --- Personal Info Split Fields ---
            if (!Schema::hasColumn('users', 'first_name')) {
                $table->string('first_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'middle_name')) {
                $table->string('middle_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('middle_name');
            }
            if (!Schema::hasColumn('users', 'suffix')) {
                $table->string('suffix', 20)->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'civil_status')) {
                $table->string('civil_status', 50)->nullable()->after('suffix');
            }
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality', 100)->nullable()->default('Filipino')->after('civil_status');
            }
            if (!Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation', 150)->nullable()->after('nationality');
            }

            // --- Profile Picture ---
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('occupation');
            }

            // --- Address Fields ---
            if (!Schema::hasColumn('users', 'house_number')) {
                $table->string('house_number', 50)->nullable()->after('profile_picture');
            }
            if (!Schema::hasColumn('users', 'street')) {
                $table->string('street', 150)->nullable()->after('house_number');
            }
            if (!Schema::hasColumn('users', 'barangay_address')) {
                $table->string('barangay_address', 150)->nullable()->after('street');
            }
            if (!Schema::hasColumn('users', 'municipality')) {
                $table->string('municipality', 150)->nullable()->after('barangay_address');
            }
            if (!Schema::hasColumn('users', 'province')) {
                $table->string('province', 150)->nullable()->after('municipality');
            }
            if (!Schema::hasColumn('users', 'zip_code')) {
                $table->string('zip_code', 10)->nullable()->after('province');
            }

            // --- Valid ID Fields ---
            if (!Schema::hasColumn('users', 'id_photo')) {
                $table->string('id_photo')->nullable()->after('zip_code');
            }
            if (!Schema::hasColumn('users', 'id_expiry_date')) {
                $table->date('id_expiry_date')->nullable()->after('id_photo');
            }
            if (!Schema::hasColumn('users', 'id_verified_at')) {
                $table->timestamp('id_verified_at')->nullable()->after('id_expiry_date');
            }
            if (!Schema::hasColumn('users', 'id_uploaded_at')) {
                $table->timestamp('id_uploaded_at')->nullable()->after('id_verified_at');
            }

            // --- Security / 2FA ---
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('id_uploaded_at');
            }

            // --- Username (separate display name) ---
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name', 'middle_name', 'last_name', 'suffix',
                'civil_status', 'nationality', 'occupation',
                'profile_picture',
                'house_number', 'street', 'barangay_address', 'municipality', 'province', 'zip_code',
                'id_photo', 'id_expiry_date', 'id_verified_at', 'id_uploaded_at',
                'two_factor_enabled',
                'username',
            ]);
        });
    }
};
