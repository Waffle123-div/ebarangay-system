<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_concern_remarks', function (Blueprint $table) {
            $table->string('attachment_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('community_concern_remarks', function (Blueprint $table) {
            $table->dropColumn('attachment_path');
        });
    }
};
