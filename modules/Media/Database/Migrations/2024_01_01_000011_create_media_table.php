<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->string('extension', 20);
            $table->unsignedBigInteger('size');
            $table->string('disk', 50)->default('public');
            $table->string('path', 500);
            $table->string('url', 1000)->nullable();
            $table->string('type', 50); // image, video, audio, document, archive, other
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration')->nullable(); // seconds for video/audio
            $table->string('status', 20)->default('processing'); // processing, ready, failed, quarantine
            $table->string('hash', 64)->nullable(); // SHA-256
            $table->boolean('is_private')->default(false);
            $table->json('meta')->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('folder_id');
            $table->index('hash');
            $table->index('mime_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
