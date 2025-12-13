<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_ticket_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ticket_type_id')->constrained('event_ticket_types')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('amount', 15, 4);
            $table->decimal('compare_amount', 15, 4)->nullable();
            $table->timestamps();

            $table->unique(['ticket_type_id', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ticket_prices');
    }
};
