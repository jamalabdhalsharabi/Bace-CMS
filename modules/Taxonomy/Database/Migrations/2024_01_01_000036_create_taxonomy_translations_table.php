<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomy_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('taxonomy_id')->constrained('taxonomies')->cascadeOnDelete();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomy_translations');
    }
};
