<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->string('location', 50)->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('type', 20)->default('custom');
            $table->uuid('linkable_id')->nullable();
            $table->string('linkable_type')->nullable();
            $table->json('title');
            $table->string('url')->nullable();
            $table->string('target', 10)->default('_self');
            $table->string('icon', 50)->nullable();
            $table->string('css_class', 100)->nullable();
            $table->unsignedInteger('ordering')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable();
            $table->timestamps();

            $table->index(['menu_id', 'parent_id']);
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
    }
};
