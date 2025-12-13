<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('key_id')->constrained('translation_keys')->cascadeOnDelete();
            $table->foreignUuid('language_id')->constrained('languages')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_machine_translated')->default(false);
            $table->foreignUuid('translated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['key_id', 'language_id']);
            $table->index('key_id');
            $table->index('language_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_values');
    }
};
