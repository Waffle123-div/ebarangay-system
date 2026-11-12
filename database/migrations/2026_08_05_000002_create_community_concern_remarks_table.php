<?php

use App\Providers\AppServiceProvider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_concern_remarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('community_concern_id')->index();
            AppServiceProvider::userForeignKeyColumn($table, 'admin_id')->index();
            $table->text('remark');
            $table->timestamps();

            $table->foreign('community_concern_id')->references('id')->on('community_concerns')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_concern_remarks');
    }
};
