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
      Schema::create('ai_analyses', function (Blueprint $table) {
    $table->id();

    $table->foreignId('analysis_run_id')
        ->constrained('analysis_runs')
        ->cascadeOnDelete();

    $table->string('status')->default('pending');

    $table->string('provider')->nullable();
    $table->string('model')->nullable();

    $table->text('summary')->nullable();

    $table->unsignedTinyInteger('score')->nullable();

    $table->json('risks')->nullable();
    $table->json('opportunities')->nullable();
    $table->json('recommendations')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');
    }
};
