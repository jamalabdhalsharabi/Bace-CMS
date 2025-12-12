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
            $table->uuidMorphs('seoable');
            $table->string('locale', 10)->default('en');
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('meta_keywords', 255)->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots', 50)->nullable();
            $table->string('og_title', 100)->nullable();
            $table->string('og_description', 200)->nullable();
            $table->string('og_image')->nullable();
            $table->string('og_type', 50)->default('article');
            $table->string('twitter_card', 50)->default('summary_large_image');
            $table->string('twitter_title', 100)->nullable();
            $table->string('twitter_description', 200)->nullable();
            $table->string('twitter_image')->nullable();
            $table->json('schema_markup')->nullable();
            $table->json('custom_meta')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id', 'locale']);
        });

        Schema::create('redirects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_path', 500)->unique();
            $table->string('target_path', 500);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_regex')->default(false);
            $table->unsignedInteger('hits_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('seo_metas');
    }
};
