<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->nullable()->constrained('translation_groups')->nullOnDelete();
            $table->string('key');
            $table->string('type', 20)->default('text');
            $table->string('source', 50)->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_deprecated')->default(false);
            $table->timestamps();

            $table->unique(['group_id', 'key']);
            $table->index('group_id');
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_keys');
    }
};
