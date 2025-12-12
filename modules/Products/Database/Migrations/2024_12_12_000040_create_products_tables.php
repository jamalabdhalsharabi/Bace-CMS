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
            $table->string('type', 50)->default('physical')->index();
            $table->string('status', 20)->default('draft')->index();
            $table->string('visibility', 20)->default('visible');
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->string('stock_status', 20)->default('in_stock')->index();
            $table->boolean('requires_shipping')->default(true);
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('weight_unit', 10)->default('kg');
            $table->string('tax_class', 50)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->json('meta')->nullable();
            $table->json('settings')->nullable();
            $table->json('dimensions')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'locale']);
            $table->unique(['slug', 'locale']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('stock_status', 20)->default('in_stock');
            $table->decimal('weight', 10, 3)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('options');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'is_active']);
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('amount', 15, 4);
            $table->decimal('compare_at_amount', 15, 4)->nullable();
            $table->decimal('cost_amount', 15, 4)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'currency_id']);
            $table->index(['variant_id', 'currency_id']);
        });

        Schema::create('product_inventories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->timestamps();

            $table->unique(['product_id', 'variant_id']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('inventory_id')->constrained('product_inventories')->cascadeOnDelete();
            $table->string('type', 50);
            $table->integer('quantity');
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference_type', 50)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at');

            $table->index(['inventory_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('product_inventories');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('products');
    }
};
