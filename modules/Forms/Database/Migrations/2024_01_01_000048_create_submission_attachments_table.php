<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->foreignUuid('field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->foreignUuid('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('path');
            $table->timestamp('created_at');

            $table->index('submission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_attachments');
    }
};
