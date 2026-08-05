<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_concerns', function (Blueprint $table) {
            $table->id();
            $table->string('concern_id', 30)->unique();
            $table->integer('user_id')->index();
            $table->string('resident_name', 150);
            $table->string('resident_email')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('title', 200);
            $table->string('category', 100)->index();
            $table->text('description');
            $table->string('location', 255);
            $table->date('incident_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status', 30)->default('Pending')->index();
            $table->text('admin_remarks')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_concerns');
    }
};
