<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->timestamps();

            $table->unique(['parent_id', 'slug']);
        });

        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableUuidMorphs('mediable');
            $table->string('collection', 50)->default('default')->index();
            $table->string('disk', 20)->default('public');
            $table->string('path');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->json('dimensions')->nullable();
            $table->json('meta')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('ordering')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('mime_type');
            $table->index(['mediable_type', 'mediable_id', 'collection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_folders');
    }
};
