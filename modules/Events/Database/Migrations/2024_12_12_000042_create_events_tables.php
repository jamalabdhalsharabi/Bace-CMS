<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->string('event_type', 50)->nullable()->index();
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('online_url')->nullable();
            $table->datetime('start_date')->index();
            $table->datetime('end_date');
            $table->string('timezone', 50)->default('UTC');
            $table->unsignedInteger('max_attendees')->nullable();
            $table->datetime('registration_deadline')->nullable();
            $table->boolean('is_free')->default(true);
            $table->foreignUuid('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        Schema::create('event_ticket_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->unsignedInteger('sold_count')->default(0);
            $table->unsignedSmallInteger('max_per_order')->default(10);
            $table->datetime('sale_start')->nullable();
            $table->datetime('sale_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('ticket_type_id')->nullable()->constrained('event_ticket_types')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('attendee_name');
            $table->string('attendee_email');
            $table->string('attendee_phone', 20)->nullable();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status', 20)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->datetime('checked_in_at')->nullable();
            $table->string('confirmation_code', 10)->unique();
            $table->timestamps();
            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('event_ticket_types');
        Schema::dropIfExists('event_translations');
        Schema::dropIfExists('events');
    }
};
