<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_block_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('static_block_id')->constrained('static_blocks')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->unique(['static_block_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_block_translations');
    }
};
