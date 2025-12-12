<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old exchange_rates table if exists and recreate with full schema
        Schema::dropIfExists('exchange_rates');
        
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('base_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignUuid('target_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 18, 8);
            $table->decimal('inverse_rate', 18, 8);
            $table->string('provider', 50)->default('manual');
            $table->boolean('is_frozen')->default(false);
            $table->timestamp('frozen_at')->nullable();
            $table->foreignUuid('frozen_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
            
            $table->unique(['base_currency_id', 'target_currency_id']);
            $table->index(['is_frozen']);
        });

        Schema::create('exchange_rate_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('base_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignUuid('target_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 18, 8);
            $table->string('provider', 50);
            $table->timestamp('recorded_at');
            
            $table->index(['base_currency_id', 'target_currency_id', 'recorded_at'], 'rate_history_currencies_idx');
        });

        Schema::create('rate_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('base_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignUuid('target_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->enum('condition', ['above', 'below', 'equals']);
            $table->decimal('threshold', 18, 8);
            $table->boolean('is_active')->default(true);
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_alerts');
        Schema::dropIfExists('exchange_rate_history');
        Schema::dropIfExists('exchange_rates');
    }
};
