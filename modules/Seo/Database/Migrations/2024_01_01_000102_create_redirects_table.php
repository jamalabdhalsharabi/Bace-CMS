<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_url', 1000);
            $table->string('target_url', 1000);
            $table->unsignedSmallInteger('status_code')->default(301); // 301 = Permanent, 302 = Temporary
            $table->boolean('is_regex')->default(false);
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->rawIndex('source_url(255)', 'redirects_source_url_index');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
