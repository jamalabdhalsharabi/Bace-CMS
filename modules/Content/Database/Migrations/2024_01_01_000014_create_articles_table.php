<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('type', 50)->default('post');
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->string('pin_position', 50)->nullable();
            $table->timestamp('pin_expires_at')->nullable();
            $table->boolean('allow_comments')->default(true);
            $table->timestamp('comments_closed_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedSmallInteger('reading_time')->default(0);
            $table->unsignedInteger('word_count')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
            $table->index('is_featured');
            $table->index(['is_pinned', 'pin_position']);
            $table->index('author_id');
            $table->index('published_at');
            $table->index('scheduled_at');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
