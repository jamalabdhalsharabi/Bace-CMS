<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('mediable');
            $table->foreignUuid('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('collection', 50)->default('default');
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamp('created_at');

            $table->unique(['mediable_type', 'mediable_id', 'media_id', 'collection'], 'content_media_unique');
            $table->index(['mediable_type', 'mediable_id', 'collection'], 'content_media_collection_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_media');
    }
};
