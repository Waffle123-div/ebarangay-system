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
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('tracking_number')->unique();
            $table->string('resident_name');
            $table->string('resident_email')->nullable();
            $table->string('document_type');
            $table->string('priority')->nullable();
            $table->string('age')->nullable();
            $table->text('purpose')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
