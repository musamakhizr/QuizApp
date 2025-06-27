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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('selected_answer_id')->nullable()->constrained('answers')->onDelete('set null');
            $table->boolean('is_skipped')->default(false);
            $table->boolean('is_correct')->default(false);
            $table->timestamp('answered_at')->useCurrent();
            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'answered_at']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
