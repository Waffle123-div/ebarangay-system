<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('document_requests', function (Blueprint $table) {
            // Adds a true/false switch defaulting to false
            $table->boolean('is_generated')->default(false)->after('status');
        });
    }

    public function down()
    {
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropColumn('is_generated');
        });
    }
};