<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('query', 500);
            $table->unsignedInteger('results_count')->default(0);
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 100)->nullable();
            $table->string('locale', 10)->nullable();
            $table->json('filters')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at');

            $table->index('query');
            $table->index('created_at');
            $table->index('results_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
