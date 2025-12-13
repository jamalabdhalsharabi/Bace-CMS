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
            $table->string('type', 50)->default('conference'); // conference, workshop, webinar, meetup, training
            $table->string('venue_type', 20)->default('physical'); // physical, virtual, hybrid
            $table->string('status', 20)->default('draft');
            $table->string('registration_status', 20)->default('closed'); // closed, open, full, waitlist
            $table->timestamp('registration_opens_at')->nullable();
            $table->timestamp('registration_closes_at')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('registered_count')->default(0);
            $table->unsignedInteger('attended_count')->default(0);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('timezone', 50)->default('UTC');
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->json('venue_coordinates')->nullable(); // {lat, lng}
            $table->string('virtual_url', 500)->nullable();
            $table->string('virtual_platform', 50)->nullable(); // zoom, teams, meet
            $table->boolean('is_free')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->index('status');
            $table->index(['starts_at', 'ends_at']);
            $table->index('registration_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
