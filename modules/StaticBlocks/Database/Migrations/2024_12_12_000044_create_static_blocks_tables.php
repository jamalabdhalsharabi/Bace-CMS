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
            $table->string('type', 50)->default('html');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('static_block_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('static_block_id')->constrained('static_blocks')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->longText('content');
            $table->timestamps();
            $table->unique(['static_block_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_block_translations');
        Schema::dropIfExists('static_blocks');
    }
};
