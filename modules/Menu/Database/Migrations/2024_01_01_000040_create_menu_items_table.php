<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->unsignedInteger('depth')->default(0);
            $table->string('type', 50); // page, article, category, custom, placeholder
            $table->string('linkable_type', 50)->nullable();
            $table->uuid('linkable_id')->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('target', 20)->default('_self'); // _self, _blank
            $table->string('icon', 100)->nullable();
            $table->string('css_class', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('visibility')->nullable(); // {roles: [], logged_in: true}
            $table->timestamps();

            $table->index(['menu_id', 'parent_id']);
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
