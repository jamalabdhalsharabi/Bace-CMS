<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('command', 500);
            $table->json('parameters')->nullable();
            $table->string('cron_expression', 100);
            $table->string('timezone', 50)->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->string('last_run_status', 20)->nullable();
            $table->unsignedInteger('last_run_duration')->nullable();
            $table->text('last_run_output')->nullable();
            $table->unsignedInteger('run_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->boolean('notify_on_failure')->default(true);
            $table->string('notify_emails', 500)->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('next_run_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_tasks');
    }
};
