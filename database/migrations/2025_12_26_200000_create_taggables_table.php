<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('taggables')) {
            Schema::create('taggables', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('taggable_type', 100);
                $table->uuid('taggable_id');
                $table->foreignUuid('taxonomy_id')->constrained('taxonomies')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamp('created_at')->nullable();

                $table->unique(['taggable_type', 'taggable_id', 'taxonomy_id']);
                $table->index(['taggable_type', 'taggable_id']);
                $table->index('taxonomy_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
    }
};
