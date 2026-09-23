<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('document_requests', function (Blueprint $table) {
            // Drop the actual column name found in your database
            if (Schema::hasColumn('document_requests', 'resident_name')) {
                $table->dropColumn('resident_name');
            }

            // Add the new separated Personal Info columns
            $table->string('last_name')->nullable()->after('user_id');
            $table->string('first_name')->nullable()->after('last_name');
            $table->string('middle_initial', 5)->nullable()->after('first_name');
            $table->string('contact_number')->nullable()->after('middle_initial');

            // Add the new separated Address columns
            $table->string('purok')->nullable()->after('contact_number');
            $table->string('street')->nullable()->after('purok');
            $table->string('city')->default('COGON EAST, CARMEN, CEBU')->after('street');
        });
    }

    public function down()
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn([
                'last_name', 'first_name', 'middle_initial', 
                'contact_number', 'purok', 'street', 'city'
            ]);
            
            $table->string('resident_name')->nullable();
        });
    }
};