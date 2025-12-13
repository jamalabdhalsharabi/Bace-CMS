<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 100)->unique();
            $table->string('type', 50)->default('contact'); // contact, newsletter, survey, application
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_captcha')->default(true);
            $table->boolean('requires_auth')->default(false);
            $table->unsignedInteger('rate_limit')->nullable(); // submissions per IP per hour
            $table->string('notification_emails', 1000)->nullable(); // comma-separated
            $table->boolean('send_confirmation')->default(false);
            $table->foreignUuid('confirmation_template_id')->nullable()->constrained('notification_templates')->nullOnDelete();
            $table->string('success_redirect', 500)->nullable();
            $table->boolean('store_submissions')->default(true);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
