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
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_category_id')->constrained('paper_categories')->onDelete('cascade');
            $table->string('title', 150)->unique(); 
            $table->text('description')->nullable();
            
            $table->integer('total_marks')->unsigned();
            $table->integer('passing_marks')->unsigned();
            $table->integer('duration_minutes')->unsigned()->nullable();
        
            $table->json('settings')->nullable();
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('shuffle_options')->default(false);
            $table->boolean('show_results_immediately')->default(false);
            $table->integer('passing_percentage')->default(50);

            $table->enum('status', ['draft', 'published', 'archived']);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('papers');
    }
};
