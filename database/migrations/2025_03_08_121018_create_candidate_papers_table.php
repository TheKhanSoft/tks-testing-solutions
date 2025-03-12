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
        Schema::create('candidate_papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_attempt_id')->constrained('test_attempts')->onDelete('cascade');
            $table->foreignId('candiate_id')->constrained()->onDeleteCascade();
            $table->foreignId('question_id')->constrained()->nullOnDelete();
            $table->foreignId('paper_id')->constrained()->nullOnDelete();
            $table->text('candidate_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->integer('marks_obtained')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->enum('status', ['unattempted', 'attempted', 'skipped', 'timeout', ''])->default('unattempted');
            $table->timestamps();
            $table->softDeletes();

            // Add index for performance on common queries
            $table->index(['is_correct', 'status']);
            
            // Add unique constraint to prevent duplicate question for the same paper to a candidate
            $table->unique(['candiate_id', 'paper_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paper_questions');
    }
};
