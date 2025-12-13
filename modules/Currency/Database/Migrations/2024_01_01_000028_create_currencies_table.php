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
            $table->string('code', 3)->unique(); // ISO 4217
            $table->string('numeric_code', 3)->nullable();
            $table->string('symbol', 10);
            $table->string('symbol_native', 10)->nullable();
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->string('decimal_separator', 5)->default('.');
            $table->string('thousands_separator', 5)->default(',');
            $table->string('symbol_position', 10)->default('before'); // before, after
            $table->string('rounding_mode', 20)->default('half_up');
            $table->decimal('rounding_increment', 10, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
