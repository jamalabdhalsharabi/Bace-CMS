<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique(); // ISO 639-1
            $table->string('locale', 20)->unique(); // en_US, ar_SA
            $table->string('name', 100);
            $table->string('native_name', 100);
            $table->string('script', 10)->nullable(); // Latn, Arab
            $table->string('direction', 3)->default('ltr'); // ltr, rtl
            $table->string('flag_icon', 20)->nullable();
            $table->foreignUuid('fallback_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('date_format', 50)->nullable()->default('YYYY-MM-DD');
            $table->string('time_format', 50)->nullable()->default('HH:mm');
            $table->json('number_format')->nullable();
            $table->unsignedTinyInteger('translation_progress')->default(0); // percentage
            $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
