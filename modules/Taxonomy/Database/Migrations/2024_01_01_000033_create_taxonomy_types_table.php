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
            $table->string('slug', 100)->unique();
            $table->boolean('is_hierarchical')->default(true);
            $table->boolean('is_multiple')->default(true);
            $table->unsignedInteger('max_depth')->nullable();
            $table->json('applies_to'); // ['service', 'article', 'product']
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomy_types');
    }
};
