<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_coupons', function (Blueprint $table) {
            $table->foreignUuid('plan_id')->constrained('pricing_plans')->cascadeOnDelete();
            $table->foreignUuid('coupon_id')->constrained('coupons')->cascadeOnDelete();

            $table->primary(['plan_id', 'coupon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_coupons');
    }
};
