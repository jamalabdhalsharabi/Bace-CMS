<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('type', 20); // percentage, fixed_amount, trial_extension
            $table->decimal('value', 15, 4);
            $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('max_uses_per_user')->nullable()->default(1);
            $table->unsignedInteger('used_count')->default(0);
            $table->json('applies_to_plans')->nullable(); // null = all plans
            $table->json('applies_to_periods')->nullable(); // ['monthly', 'yearly']
            $table->string('duration', 20)->default('once'); // once, forever, repeating
            $table->unsignedInteger('duration_months')->nullable();
            $table->boolean('first_payment_only')->default(false);
            $table->decimal('min_amount', 15, 4)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index(['is_active', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
