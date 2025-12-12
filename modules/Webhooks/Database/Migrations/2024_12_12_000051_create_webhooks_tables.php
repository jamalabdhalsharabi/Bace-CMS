<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('url', 500);
            $table->string('secret', 64)->nullable();
            $table->json('events');
            $table->json('headers')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedInteger('failure_count')->default(0);
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event', 100);
            $table->json('payload');
            $table->unsignedSmallInteger('response_status')->default(0);
            $table->text('response_body')->nullable();
            $table->float('response_time')->nullable();
            $table->boolean('is_successful')->default(false)->index();
            $table->timestamp('created_at');

            $table->index(['webhook_id', 'created_at']);
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableUuidMorphs('mailable');
            $table->string('to');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject');
            $table->longText('body')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('error')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhooks');
    }
};
