<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 50)->unique();
            $table->string('type', 20)->default('subscription');
            $table->unsignedSmallInteger('trial_days')->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_recommended')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('billing_periods');
            $table->json('meta')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

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

        Schema::create('plan_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->string('billing_period', 20);
            $table->decimal('amount', 10, 2);
            $table->decimal('compare_amount', 10, 2)->nullable();
            $table->decimal('setup_fee', 10, 2)->nullable();
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'currency_id', 'billing_period']);
        });

        Schema::create('plan_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('feature_key', 50);
            $table->string('value')->nullable();
            $table->string('type', 20)->default('boolean');
            $table->boolean('is_highlighted')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['plan_id', 'feature_key']);
        });

        Schema::create('plan_feature_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('feature_id')->constrained('plan_features')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('label');
            $table->string('tooltip')->nullable();
            $table->timestamps();
            $table->unique(['feature_id', 'locale']);
        });

        Schema::create('plan_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('resource', 50);
            $table->integer('limit_value')->nullable();
            $table->string('period', 20)->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'resource']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->string('billing_period', 20);
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resume_at')->nullable();
            $table->foreignUuid('pending_plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete();
            $table->string('payment_method', 50)->nullable();
            $table->string('external_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('resource', 50);
            $table->integer('quantity');
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();
            $table->index(['subscription_id', 'resource', 'period_start']);
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->string('type', 20)->default('percentage');
            $table->decimal('value', 10, 2);
            $table->json('applies_to_plans')->nullable();
            $table->json('applies_to_periods')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedSmallInteger('per_user_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('first_payment_only')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('subscription_usages');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plan_limits');
        Schema::dropIfExists('plan_feature_translations');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('plan_prices');
        Schema::dropIfExists('pricing_plan_translations');
        Schema::dropIfExists('pricing_plans');
    }
};
