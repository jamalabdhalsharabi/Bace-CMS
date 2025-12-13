<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, new, opened, in_progress, on_hold, completed, spam, archived
            $table->boolean('is_spam')->default(false);
            $table->decimal('spam_score', 5, 2)->nullable();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('source_url', 1000)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('locale', 10)->nullable();
            $table->json('data');
            $table->string('tracking_code', 50)->nullable()->unique();
            $table->timestamp('opened_at')->nullable();
            $table->foreignUuid('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('form_id');
            $table->index('status');
            $table->index('assigned_to');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
