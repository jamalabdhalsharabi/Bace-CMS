<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rate_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('exchange_rate_id')->constrained('exchange_rates')->cascadeOnDelete();
            $table->decimal('rate', 20, 10);
            $table->string('source', 50);
            $table->string('provider', 50)->nullable();
            $table->timestamp('recorded_at')->useCurrent();

            $table->index('exchange_rate_id');
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_history');
    }
};
