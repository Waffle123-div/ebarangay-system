<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('document_requests', 'id_type')) {
                $table->string('id_type')->nullable()->after('document_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (Schema::hasColumn('document_requests', 'id_type')) {
                $table->dropColumn('id_type');
            }
        });
    }
};
