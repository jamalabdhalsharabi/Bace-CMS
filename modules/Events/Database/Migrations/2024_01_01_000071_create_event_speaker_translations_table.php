<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_speaker_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('speaker_id')->constrained('event_speakers')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->text('bio')->nullable();
            $table->text('expertise')->nullable();
            $table->timestamps();

            $table->unique(['speaker_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_speaker_translations');
    }
};
