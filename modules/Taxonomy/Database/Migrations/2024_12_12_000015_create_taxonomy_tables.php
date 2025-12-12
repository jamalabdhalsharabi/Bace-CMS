<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomy_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 50)->unique();
            $table->json('name');
            $table->boolean('is_hierarchical')->default(false);
            $table->boolean('is_multiple')->default(true);
            $table->json('applies_to')->nullable();
            $table->timestamps();
        });

        Schema::create('taxonomies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('type_id')->constrained('taxonomy_types')->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('taxonomies')->nullOnDelete();
            $table->foreignUuid('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('ordering')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type_id', 'parent_id']);
            $table->index('is_active');
        });

        Schema::create('taxonomy_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('taxonomy_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();

            $table->unique(['taxonomy_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index('locale');
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignUuid('taxonomy_id')->constrained()->cascadeOnDelete();
            $table->uuidMorphs('taggable');

            $table->primary(['taxonomy_id', 'taggable_id', 'taggable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('taxonomy_translations');
        Schema::dropIfExists('taxonomies');
        Schema::dropIfExists('taxonomy_types');
    }
};
