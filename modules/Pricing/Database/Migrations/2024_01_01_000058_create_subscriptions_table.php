<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, trial, active, paused, past_due, cancelled, expired
            $table->string('billing_period', 20);
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('amount', 15, 4);
            $table->foreignUuid('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->decimal('discount_amount', 15, 4)->nullable();
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('trial_ends_at')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resume_at')->nullable();
            $table->foreignUuid('pending_plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();
            $table->timestamp('pending_effective_at')->nullable();
            $table->string('gateway', 50)->nullable(); // stripe, paddle
            $table->string('gateway_subscription_id')->nullable();
            $table->string('gateway_customer_id')->nullable();
            $table->timestamps();
            $table->timestamp('ends_at')->nullable();

            $table->index('user_id');
            $table->index('plan_id');
            $table->index('status');
            $table->index('current_period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
