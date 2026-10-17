<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'purok')) {
                $table->string('purok', 100)->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('users', 'sitio')) {
                $table->string('sitio', 100)->nullable()->after('purok');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['purok', 'sitio']);
        });
    }
};
