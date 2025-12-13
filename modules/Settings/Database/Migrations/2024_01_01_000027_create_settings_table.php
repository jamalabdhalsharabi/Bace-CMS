<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->nullable()->constrained('setting_groups')->nullOnDelete();
            $table->string('key', 255)->unique();
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, integer, boolean, json, array, file, color
            $table->json('validation_rules')->nullable();
            $table->json('options')->nullable(); // for select type
            $table->text('default_value')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('autoload')->default(true);
            $table->timestamps();

            $table->index('key');
            $table->index('autoload');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
