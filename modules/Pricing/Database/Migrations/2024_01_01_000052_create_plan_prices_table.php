<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->string('billing_period', 20); // monthly, quarterly, yearly, lifetime
            $table->decimal('amount', 15, 4);
            $table->decimal('setup_fee', 15, 4)->nullable()->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->unique(['plan_id', 'currency_id', 'billing_period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};
