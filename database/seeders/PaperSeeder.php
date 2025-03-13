<?php

namespace Database\Seeders;

use App\Models\Paper;
use App\Models\PaperCategory;
use Illuminate\Database\Seeder;

class PaperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get paper categories
        $programmingCategory = PaperCategory::where('name', 'Programming')->first();
        $mathematicsCategory = PaperCategory::where('name', 'Mathematics')->first();
        $scienceCategory = PaperCategory::where('name', 'Science')->first();
        $generalCategory = PaperCategory::where('name', 'General Knowledge')->first();
        
        // Create default category if none of the specific categories exist
        $defaultCategory = $programmingCategory ?? $mathematicsCategory ?? $scienceCategory ?? $generalCategory;
        
        if (!$defaultCategory) {
            $defaultCategory = PaperCategory::create([
                'name' => 'General Knowledge',
                'description' => 'General knowledge tests covering various subjects',
            ]);
        }

        // Create existing papers (1-3)
        Paper::create([
            'paper_category_id' => $defaultCategory->id,
            'title' => 'Programming Basics Test',
            'description' => 'Test your knowledge of programming fundamentals',
            'total_marks' => 50,
            'passing_marks' => 25,
            'duration_minutes' => 60,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'show_results_immediately' => false,
            'passing_percentage' => 50,
            'status' => 'published',
        ]);
        
        Paper::create([
            'paper_category_id' => $defaultCategory->id,
            'title' => 'Data Structures and Algorithms',
            'description' => 'Advanced test on data structures and algorithms',
            'total_marks' => 100,
            'passing_marks' => 60,
            'duration_minutes' => 120,
            'shuffle_questions' => true,
            'shuffle_options' => false,
            'show_results_immediately' => false,
            'passing_percentage' => 60,
            'status' => 'published',
        ]);
        
        Paper::create([
            'paper_category_id' => $defaultCategory->id,
            'title' => 'Database Management Systems',
            'description' => 'Test covering SQL and database design concepts',
            'total_marks' => 75,
            'passing_marks' => 45,
            'duration_minutes' => 90,
            'shuffle_questions' => false,
            'shuffle_options' => true,
            'show_results_immediately' => false,
            'passing_percentage' => 60,
            'status' => 'draft',
        ]);

        // Create paper 4: Mathematics Test
        Paper::create([
            'paper_category_id' => $mathematicsCategory ? $mathematicsCategory->id : $defaultCategory->id,
            'title' => 'Mathematics Test',
            'description' => 'Test covering various calculus and mathematical concepts',
            'total_marks' => 50,
            'passing_marks' => 25,
            'duration_minutes' => 60,
            'shuffle_questions' => false,
            'shuffle_options' => true,
            'show_results_immediately' => false,
            'passing_percentage' => 50,
            'status' => 'published',
        ]);
        
        // Create paper 5: Physics Test
        Paper::create([
            'paper_category_id' => $scienceCategory ? $scienceCategory->id : $defaultCategory->id,
            'title' => 'Physics Test',
            'description' => 'Comprehensive test on physics fundamentals and theories',
            'total_marks' => 60,
            'passing_marks' => 30,
            'duration_minutes' => 75,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'show_results_immediately' => false,
            'passing_percentage' => 50,
            'status' => 'published',
        ]);
        
        // Create paper 6: Chemistry Test
        Paper::create([
            'paper_category_id' => $scienceCategory ? $scienceCategory->id : $defaultCategory->id,
            'title' => 'Chemistry Test',
            'description' => 'Test covering inorganic and organic chemistry concepts',
            'total_marks' => 55,
            'passing_marks' => 28,
            'duration_minutes' => 70,
            'shuffle_questions' => true,
            'shuffle_options' => false,
            'show_results_immediately' => false,
            'passing_percentage' => 50,
            'status' => 'published',
        ]);
    }
}
