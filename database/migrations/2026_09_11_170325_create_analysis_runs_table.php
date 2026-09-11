<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_runs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('file_id')
                ->constrained('files')
                ->cascadeOnDelete();

            $table->string('status')->default('completed');

            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('profit_margin', 8, 2)->default(0);

            $table->unsignedInteger('row_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_runs');
    }
};
