<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 100);
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('notifiable_type', 50)->nullable();
            $table->uuid('notifiable_id')->nullable();
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->json('channels')->nullable(); // ['database', 'email', 'push']
            $table->json('sent_channels')->nullable();
            $table->timestamp('created_at');

            $table->index('user_id');
            $table->index('type');
            $table->index(['user_id', 'read_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
