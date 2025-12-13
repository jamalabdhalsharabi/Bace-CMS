<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('type_id')->constrained('taxonomy_types')->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('taxonomies')->nullOnDelete();
            $table->foreignUuid('image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('depth')->default(0);
            $table->string('path', 1000)->nullable();
            $table->string('slug');
            $table->string('icon', 100)->nullable();
            $table->string('color', 20)->nullable();
            $table->unsignedInteger('content_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type_id', 'parent_id']);
            $table->index('is_active');
            $table->index('slug');
            $table->index('path');
            $table->index('depth');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomies');
    }
};
