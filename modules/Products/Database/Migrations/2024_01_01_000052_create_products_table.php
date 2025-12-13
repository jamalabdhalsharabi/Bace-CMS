<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable();
            $table->string('type', 50)->default('physical'); // physical, digital, virtual, subscription
            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('visibility', 20)->default('visible'); // visible, hidden, catalog_only, search_only
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->string('stock_status', 20)->default('in_stock'); // in_stock, out_of_stock, on_backorder
            $table->boolean('requires_shipping')->default(true);
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('weight_unit', 10)->nullable()->default('kg');
            $table->string('tax_class', 50)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->json('settings')->nullable();
            $table->json('dimensions')->nullable(); // {length, width, height, unit}
            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index('type');
            $table->index('status');
            $table->index('stock_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
