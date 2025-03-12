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
        Schema::create('paper_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained('papers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('questions')->onDelete('cascade');
            $table->float('percentage')->default(0.0); // Default value added
            $table->integer('number_of_questions')->unsigned();
            $table->json('difficulty_distribution')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint to prevent duplicate subject in a paper
            $table->unique(['paper_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paper_subjects');
    }
};
