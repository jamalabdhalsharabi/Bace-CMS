<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('revisionable_type', 50);
            $table->uuid('revisionable_id');
            $table->unsignedInteger('version');
            $table->string('type', 20)->default('update'); // create, update, publish, unpublish, restore
            $table->json('old_data')->nullable();
            $table->json('new_data');
            $table->json('diff')->nullable();
            $table->text('summary')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at');

            $table->unique(['revisionable_type', 'revisionable_id', 'version']);
            $table->index(['revisionable_type', 'revisionable_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
