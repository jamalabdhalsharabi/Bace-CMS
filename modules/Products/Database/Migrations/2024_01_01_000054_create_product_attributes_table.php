<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 50)->unique();
            $table->string('type', 20)->default('select');
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_variation')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_filterable');
            $table->index('is_variation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
