<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('author_name');
            $table->string('author_title')->nullable();
            $table->string('author_company')->nullable();
            $table->foreignUuid('author_avatar_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('source', 50)->nullable();
            $table->string('source_url')->nullable();
            $table->date('date')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('testimonial_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('testimonial_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->timestamps();
            $table->unique(['testimonial_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial_translations');
        Schema::dropIfExists('testimonials');
    }
};
