<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plans', function (Blueprint $table) {
            $table->foreignUuid('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->json('meta')->nullable();

            $table->primary(['service_id', 'plan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
