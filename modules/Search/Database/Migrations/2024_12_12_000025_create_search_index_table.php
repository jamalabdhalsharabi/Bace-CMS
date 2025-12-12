<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_index', function (Blueprint $table) {
            $table->id();
            $table->string('index_name', 50)->index();
            $table->string('document_id', 36)->index();
            $table->json('content');
            $table->text('searchable_text');
            $table->timestamp('updated_at');

            $table->unique(['index_name', 'document_id']);
            $table->fullText('searchable_text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_index');
    }
};
