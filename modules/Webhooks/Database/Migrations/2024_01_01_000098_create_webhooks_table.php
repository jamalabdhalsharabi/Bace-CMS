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
            $table->string('name');
            $table->string('url', 1000);
            $table->json('events'); // ['article.published', 'order.created']
            $table->string('secret')->nullable();
            $table->json('headers')->nullable();
            $table->unsignedInteger('timeout')->default(30); // seconds
            $table->unsignedInteger('retry_count')->default(3);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->string('last_status', 20)->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
