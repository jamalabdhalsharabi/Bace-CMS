<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'locale']);
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_translations');
    }
};