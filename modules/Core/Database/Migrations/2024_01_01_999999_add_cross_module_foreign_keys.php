<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users -> Media (avatar)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('avatar_id')->references('id')->on('media')->nullOnDelete();
        });

        // Media -> Users (uploaded_by)
        Schema::table('media', function (Blueprint $table) {
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        // Media Folders -> Users (created_by)
        Schema::table('media_folders', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['avatar_id']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });

        Schema::table('media_folders', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });
    }
};
