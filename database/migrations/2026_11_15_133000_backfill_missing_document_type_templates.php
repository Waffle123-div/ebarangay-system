<?php

use Database\Seeders\DocumentTypeSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new DocumentTypeSeeder())->backfillMissingTemplates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep assigned document templates intact
    }
};
