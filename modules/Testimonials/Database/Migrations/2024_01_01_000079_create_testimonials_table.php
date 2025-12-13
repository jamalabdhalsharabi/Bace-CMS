<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source', 50)->default('manual'); // manual, form, import, request
            $table->string('status', 20)->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->string('client_name');
            $table->string('client_title')->nullable();
            $table->string('client_company')->nullable();
            $table->foreignUuid('client_photo_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('client_website')->nullable();
            $table->decimal('rating', 2, 1)->nullable(); // 1.0 to 5.0
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('verification_method', 50)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('published_at')->nullable();

            $table->index('status');
            $table->index('is_featured');
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
