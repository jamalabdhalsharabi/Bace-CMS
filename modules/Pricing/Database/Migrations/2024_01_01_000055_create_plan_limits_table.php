<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('resource', 100);
            $table->integer('limit_value')->nullable();
            $table->string('period', 20)->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'resource']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_limits');
    }
};
