<?php

use Illuminate\Database\Migrations\Migration;
use Database\Seeders\DocumentTypeSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        (new DocumentTypeSeeder())->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reversal needed
    }
};

