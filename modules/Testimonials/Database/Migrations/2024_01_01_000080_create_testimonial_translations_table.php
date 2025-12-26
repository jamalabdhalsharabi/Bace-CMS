<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonial_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('testimonial_id')->constrained('testimonials')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->text('content');
            $table->timestamps();

            $table->unique(['testimonial_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_translations');
    }
};
