<?php

namespace Database\Seeders;

use App\Models\Paper;
use Illuminate\Database\Seeder;

class PaperSeeder extends Seeder
{
    public function run(): void
    {
        $papers = [
            // Entry Test
            [
                'paper_category_id' => 3, // Entry Test Category
                'title' => 'Computer Science Entry Test',
                'description' => 'Entrance test for Bachelor of Computer Science program',
                'total_marks' => 100,
                'passing_marks' => 50,
                'duration_minutes' => 120,
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'show_results_immediately' => false,
                'passing_percentage' => 50,
                'status' => 'published',
            ],
            
            // GAT General
            [
                'paper_category_id' => 1, // GAT General
                'title' => 'GAT General - October 2023',
                'description' => 'Graduate Assessment Test for general subjects',
                'total_marks' => 100,
                'passing_marks' => 50,
                'duration_minutes' => 180,
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'show_results_immediately' => false,
                'passing_percentage' => 50,
                'status' => 'published',
            ],
            
            // Midterm
            [
                'paper_category_id' => 4, // Midterm Exam
                'title' => 'Programming Fundamentals Midterm',
                'description' => 'Midterm examination for Programming Fundamentals course',
                'total_marks' => 50,
                'passing_marks' => 25,
                'duration_minutes' => 90,
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'show_results_immediately' => false,
                'passing_percentage' => 50,
                'status' => 'published',
            ],
            
            // Final Exam
            [
                'paper_category_id' => 5, // Final Exam
                'title' => 'Database Systems Final',
                'description' => 'Final examination for Database Systems course',
                'total_marks' => 100,
                'passing_marks' => 50,
                'duration_minutes' => 180,
                'shuffle_questions' => true,
                'shuffle_options' => true,
                'show_results_immediately' => false,
                'passing_percentage' => 50,
                'status' => 'draft',
            ],
        ];

        foreach ($papers as $paper) {
            Paper::create($paper);
        }
    }
}
