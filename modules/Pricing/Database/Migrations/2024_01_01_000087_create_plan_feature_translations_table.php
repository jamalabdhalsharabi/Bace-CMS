<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_feature_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('feature_id')->constrained('plan_features')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('label');
            $table->string('tooltip')->nullable();
            $table->timestamps();

            $table->unique(['feature_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_feature_translations');
    }
};
