<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomy_type_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('type_id')->constrained('taxonomy_types')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 100);
            $table->string('name_singular', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['type_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxonomy_type_translations');
    }
};
