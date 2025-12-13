<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('feature_key', 100);
            $table->string('value')->nullable();
            $table->string('type', 20)->default('boolean');
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'feature_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
