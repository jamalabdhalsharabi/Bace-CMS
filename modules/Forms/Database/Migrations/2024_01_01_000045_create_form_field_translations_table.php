<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_field_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('label');
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->string('error_message', 500)->nullable();
            $table->timestamps();

            $table->unique(['field_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_field_translations');
    }
};
