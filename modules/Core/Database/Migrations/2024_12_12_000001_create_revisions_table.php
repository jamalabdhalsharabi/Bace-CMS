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
            $table->string('revisionable_type');
            $table->uuid('revisionable_id');
            $table->uuid('user_id')->nullable();
            $table->unsignedInteger('revision_number')->default(1);
            $table->json('data')->nullable();
            $table->json('changes')->nullable();
            $table->string('summary')->nullable();
            $table->boolean('is_auto')->default(true);
            $table->timestamp('created_at');

            $table->index(['revisionable_type', 'revisionable_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
