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
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('type', 20)->default('contact');
            $table->json('success_message')->nullable();
            $table->json('notification_emails')->nullable();
            $table->string('redirect_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('captcha_enabled')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->json('label');
            $table->string('type', 20)->default('text');
            $table->json('placeholder')->nullable();
            $table->string('default_value')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('ordering')->default(0);
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'ordering']);
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('data');
            $table->string('ip_address', 45);
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('status', 20)->default('new');
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
