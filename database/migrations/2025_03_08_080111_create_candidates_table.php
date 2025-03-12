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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('father_name', 100);
            $table->enum('gender', ['male', 'female', 'other']); 
            $table->date('dob');
            $table->string('id_card_no', 20);
            $table->string('password', 255);
            $table->string('email', 150);
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'banned'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Add unique constraint to prevent duplicate questions in a paper
            $table->unique(['paper_id', 'question_id']);
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
