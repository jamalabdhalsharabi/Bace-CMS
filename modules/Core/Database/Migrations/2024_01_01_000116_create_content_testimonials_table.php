<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_testimonials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('testimoniable');
            $table->foreignUuid('testimonial_id')->constrained('testimonials')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('created_at');

            $table->unique(['testimoniable_type', 'testimoniable_id', 'testimonial_id'], 'content_testimonials_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_testimonials');
    }
};
