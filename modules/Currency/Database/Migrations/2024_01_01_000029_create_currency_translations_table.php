<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 100);
            $table->string('name_plural', 100)->nullable();
            $table->timestamps();

            $table->unique(['currency_id', 'locale']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_translations');
    }
};
