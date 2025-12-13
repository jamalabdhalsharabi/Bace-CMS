<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_template_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('notification_templates')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('email_subject')->nullable();
            $table->longText('email_body')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('sms_body', 500)->nullable();
            $table->timestamps();

            $table->unique(['template_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_template_translations');
    }
};
