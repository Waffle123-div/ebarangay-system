<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable();
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable();
            }
            if (!Schema::hasColumn('users', 'proof_of_residency')) {
                $table->string('proof_of_residency')->nullable();
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('incomplete');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birthdate', 'gender', 'proof_of_residency']);
        });
    }
};