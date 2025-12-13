<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('from_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignUuid('to_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 20, 10);
            $table->string('source', 50)->default('manual'); // manual, api, bank
            $table->string('provider', 50)->nullable();
            $table->boolean('is_frozen')->default(false);
            $table->timestamp('frozen_at')->nullable();
            $table->foreignUuid('frozen_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('frozen_until')->nullable();
            $table->timestamp('effective_at')->useCurrent();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['from_currency_id', 'to_currency_id']);
            $table->index(['from_currency_id', 'to_currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
