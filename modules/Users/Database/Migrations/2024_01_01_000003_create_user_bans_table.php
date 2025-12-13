<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_bans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamp('banned_until')->nullable();
            $table->foreignUuid('banned_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('lifted_at')->nullable();
            $table->foreignUuid('lifted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at');

            $table->index(['user_id', 'banned_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bans');
    }
};
