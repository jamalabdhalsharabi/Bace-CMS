<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->foreignUuid('field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['submission_id', 'field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_field_values');
    }
};
