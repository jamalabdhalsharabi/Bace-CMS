<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('uuid')->unique();
            $table->string('connection', 100);
            $table->string('queue', 100);
            $table->text('payload');
            $table->text('exception');
            $table->timestamp('failed_at')->useCurrent();

            $table->index('queue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
    }
};
