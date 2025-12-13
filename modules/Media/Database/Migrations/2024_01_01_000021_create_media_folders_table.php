<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('media_folders')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('path', 1000)->nullable();
            $table->unsignedInteger('depth')->default(0);
            $table->unsignedInteger('files_count')->default(0);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('parent_id');
            $table->index('path');
            $table->unique(['parent_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_folders');
    }
};
