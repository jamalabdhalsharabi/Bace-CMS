<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plan_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('short_description')->nullable();
            $table->string('cta_text', 50)->nullable();
            $table->string('badge_text', 30)->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_plan_translations');
    }
};
