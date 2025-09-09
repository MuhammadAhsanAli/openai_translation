<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the translations table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('title', 191)->index();
            $table->text('description');
            $table->json('translated')->nullable();
            $table->string('target_language', 2)->nullable()->index();
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('pending')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
