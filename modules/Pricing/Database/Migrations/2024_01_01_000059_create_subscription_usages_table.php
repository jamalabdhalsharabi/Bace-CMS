<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->string('resource', 100);
            $table->unsignedInteger('used')->default(0);
            $table->unsignedInteger('limit_value')->nullable();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->timestamps();

            $table->unique(['subscription_id', 'resource', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_usages');
    }
};
