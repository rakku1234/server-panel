<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eggs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('origin_id')->unique();
            $table->uuid('uuid')->unique();
            $table->string('name')->default('');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->json('docker_images')->nullable();
            $table->json('variables')->nullable();
            $table->text('startup');
            $table->string('slug')->nullable()->unique();
            $table->boolean('public')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eggs');
    }
};
