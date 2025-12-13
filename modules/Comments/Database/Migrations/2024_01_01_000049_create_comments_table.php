<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('commentable_type', 50); // article, product, event
            $table->uuid('commentable_id');
            $table->foreignUuid('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->unsignedInteger('depth')->default(0);
            $table->text('content');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name', 100)->nullable(); // for guests
            $table->string('author_email')->nullable(); // for guests
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, spam, hidden
            $table->boolean('is_spam')->default(false);
            $table->decimal('spam_score', 5, 2)->nullable();
            $table->unsignedInteger('upvotes')->default(0);
            $table->unsignedInteger('downvotes')->default(0);
            $table->unsignedInteger('report_count')->default(0);
            $table->boolean('is_pinned')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_type', 'commentable_id']);
            $table->index('parent_id');
            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
