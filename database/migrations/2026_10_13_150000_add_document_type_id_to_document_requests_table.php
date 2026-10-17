<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add document_type_id column matching document_types.id (bigint(20) unsigned)
        Schema::table('document_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('document_requests', 'document_type_id')) {
                $table->unsignedBigInteger('document_type_id')->nullable()->after('resident_email');
            }
        });

        // 2. Backfill document_type_id from existing document_type records
        if (Schema::hasTable('document_types')) {
            $docTypes = DB::table('document_types')->select('id', 'name')->get();
            foreach ($docTypes as $docType) {
                DB::table('document_requests')
                    ->where('document_type', $docType->name)
                    ->whereNull('document_type_id')
                    ->update(['document_type_id' => $docType->id]);
            }

            // Also check for multiple or bundled requests containing the document type name
            $unmatched = DB::table('document_requests')
                ->whereNull('document_type_id')
                ->whereNotNull('document_type')
                ->get();

            foreach ($unmatched as $req) {
                foreach ($docTypes as $docType) {
                    if (stripos($req->document_type, $docType->name) !== false) {
                        DB::table('document_requests')
                            ->where('id', $req->id)
                            ->update(['document_type_id' => $docType->id]);
                        break;
                    }
                }
            }
        }

        // 3. Add foreign key constraint linking document_requests.document_type_id to document_types.id
        Schema::table('document_requests', function (Blueprint $table) {
            $table->foreign('document_type_id')
                  ->references('id')
                  ->on('document_types')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });

        // 4. Ensure document_type varchar column is nullable for flexible request generation
        if (Schema::hasColumn('document_requests', 'document_type')) {
            DB::statement('ALTER TABLE document_requests MODIFY document_type VARCHAR(100) NULL;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            if (Schema::hasColumn('document_requests', 'document_type_id')) {
                $table->dropForeign(['document_type_id']);
                $table->dropColumn('document_type_id');
            }
        });
    }
};
