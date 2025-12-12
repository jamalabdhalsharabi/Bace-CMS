<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 3)->unique();
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->enum('symbol_position', ['before', 'after'])->default('before');
            $table->char('decimal_separator', 1)->default('.');
            $table->char('thousand_separator', 1)->default(',');
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('ordering')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_default');
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('from_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignUuid('to_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 16, 8);
            $table->string('source', 50)->default('manual');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->index(['from_currency_id', 'to_currency_id', 'fetched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }
};
