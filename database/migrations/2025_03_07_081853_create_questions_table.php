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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDeleteCascade();
            $table->foreignId('question_type_id')->constrained('question_types')->onDeleteCascade();
            $table->integer('max_time_allowed')->unsigned()->nullable();
            $table->tinyinteger('negative_marks')->unsigned()->default(0); 
            $table->text('text');
            $table->text('description')->nullable();
            $table->text('explanation')->nullable();
            $table->text('image')->nullable();
            $table->integer('marks')->default(1);
            $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'very_hard', 'expert'])->default('medium'); 
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Add index on commonly filtered columns
            $table->index(['subject_id', 'question_type_id', 'difficulty_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
