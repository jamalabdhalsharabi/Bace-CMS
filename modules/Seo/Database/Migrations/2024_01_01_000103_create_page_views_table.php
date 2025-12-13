<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('url', 1000);
            $table->nullableUuidMorphs('viewable');
            $table->string('visitor_id', 100)->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('utm_term', 100)->nullable();
            $table->string('utm_content', 100)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('os_version', 20)->nullable();
            $table->char('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');

            $table->rawIndex('url(255)', 'page_views_url_index');
            $table->index('visitor_id');
            $table->index('created_at');
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
