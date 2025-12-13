<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('type', 20)->default('text');
            $table->string('default_value')->nullable();
            $table->json('options')->nullable();
            $table->string('allowed_extensions')->nullable();
            $table->unsignedInteger('max_file_size')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('ordering')->default(0);
            $table->string('width', 20)->default('full');
            $table->string('css_class')->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'ordering']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
