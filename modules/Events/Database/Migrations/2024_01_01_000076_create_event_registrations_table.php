<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending, confirmed, cancelled, refunded, no_show, attended
            $table->string('attendee_name');
            $table->string('attendee_email');
            $table->string('attendee_phone', 50)->nullable();
            $table->json('custom_data')->nullable();
            $table->string('payment_status', 20)->nullable();
            $table->decimal('payment_amount', 15, 4)->nullable();
            $table->foreignUuid('payment_currency')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignUuid('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->decimal('discount_amount', 15, 4)->nullable();
            $table->string('confirmation_code', 50)->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignUuid('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 50)->nullable();
            $table->timestamps();

            $table->index('event_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('attendee_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
