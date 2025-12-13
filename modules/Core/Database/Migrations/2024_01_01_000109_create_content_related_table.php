<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_related', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_type', 50);
            $table->uuid('source_id');
            $table->string('related_type', 50);
            $table->uuid('related_id');
            $table->string('relation_type', 30)->default('similar');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('created_at');

            $table->unique(['source_type', 'source_id', 'related_type', 'related_id'], 'content_related_unique');
            $table->index(['source_type', 'source_id', 'relation_type'], 'content_related_source_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_related');
    }
};
