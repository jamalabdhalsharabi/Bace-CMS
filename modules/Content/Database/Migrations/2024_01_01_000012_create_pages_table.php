<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_homepage')->default(false);
            $table->boolean('is_system')->default(false);
            $table->string('template', 100)->default('default');
            $table->foreignUuid('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->unsignedInteger('depth')->default(0);
            $table->string('path', 1000)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('sections')->nullable();
            $table->json('meta')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
            $table->index('path');
            $table->index('template');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
