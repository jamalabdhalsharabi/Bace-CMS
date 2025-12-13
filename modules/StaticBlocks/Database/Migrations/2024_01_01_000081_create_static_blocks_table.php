<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identifier', 100)->unique();
            $table->string('type', 50)->default('html'); // html, banner, cta, hero, features, stats, testimonials, newsletter
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->json('visibility_rules')->nullable();
            $table->timestamp('show_from')->nullable();
            $table->timestamp('show_until')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('published_at')->nullable();

            $table->index('identifier');
            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_blocks');
    }
};
