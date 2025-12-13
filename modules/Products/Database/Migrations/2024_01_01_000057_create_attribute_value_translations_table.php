<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_value_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('label');
            $table->timestamps();

            $table->unique(['value_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_translations');
    }
};
