<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('vote');
            $table->timestamp('created_at');

            $table->unique(['comment_id', 'user_id']);
            $table->index('vote');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_votes');
    }
};
