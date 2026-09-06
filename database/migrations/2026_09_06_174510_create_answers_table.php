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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attempt_id')
                ->constrained('attempts')
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained('questions')
                ->restrictOnDelete();

            $table->foreignId('option_id')
                ->constrained('options')
                ->restrictOnDelete();

            $table->unique(['attempt_id', 'question_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
