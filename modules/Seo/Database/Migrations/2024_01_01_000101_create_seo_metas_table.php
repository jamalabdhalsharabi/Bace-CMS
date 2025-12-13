<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('seoable_type', 50);
            $table->uuid('seoable_id');
            $table->string('locale', 10);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('keywords', 500)->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->foreignUuid('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('og_type', 50)->nullable()->default('website');
            $table->string('twitter_card', 50)->nullable()->default('summary_large_image');
            $table->string('twitter_title')->nullable();
            $table->text('twitter_description')->nullable();
            $table->foreignUuid('twitter_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('robots', 100)->nullable(); // index, follow
            $table->string('canonical_url', 1000)->nullable();
            $table->json('schema_markup')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id', 'locale']);
            $table->index(['seoable_type', 'seoable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
