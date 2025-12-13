<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_checkins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignUuid('registration_id')->constrained('event_registrations')->cascadeOnDelete();
            $table->foreignUuid('ticket_id')->nullable()->constrained('event_tickets')->nullOnDelete();
            $table->foreignUuid('session_id')->nullable()->constrained('event_sessions')->nullOnDelete();
            $table->string('check_type', 20)->default('in');
            $table->string('method', 20)->default('manual');
            $table->foreignUuid('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at');

            $table->index(['event_id', 'created_at']);
            $table->index(['registration_id', 'check_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_checkins');
    }
};
