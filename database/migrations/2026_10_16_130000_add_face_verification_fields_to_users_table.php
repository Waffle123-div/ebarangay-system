<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'selfie_photo')) {
                $table->string('selfie_photo')->nullable()->after('proof_of_residency');
            }
            if (!Schema::hasColumn('users', 'face_verified_at')) {
                $table->timestamp('face_verified_at')->nullable()->after('selfie_photo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'face_verified_at')) {
                $table->dropColumn('face_verified_at');
            }
            if (Schema::hasColumn('users', 'selfie_photo')) {
                $table->dropColumn('selfie_photo');
            }
        });
    }
};
