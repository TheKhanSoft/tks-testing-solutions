<?php

namespace Database\Seeders;

use App\Models\Paper;
use App\Models\PaperSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class PaperSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get papers - make sure they exist
        $papers = Paper::all();
        
        // If no papers exist, there's nothing to do
        if ($papers->isEmpty()) {
            $this->command->info('No papers found. Please run the PaperSeeder first.');
            return;
        }
        
        // Get subjects
        $subjects = Subject::all();
        
        // If no subjects exist, there's nothing to do
        if ($subjects->isEmpty()) {
            $this->command->info('No subjects found. Please run the SubjectSeeder first.');
            return;
        }
        
        // For the first paper, add Programming Fundamentals and Data Structures
        $programmingPaper = $papers->firstWhere('title', 'Programming Basics Test');
        if ($programmingPaper) {
            $progFundamentals = $subjects->firstWhere('id', 1);
            if ($progFundamentals) {
                PaperSubject::create([
                    'paper_id' => $programmingPaper->id,
                    'subject_id' => $progFundamentals->id,
                    'percentage' => 60,
                    'number_of_questions' => 15,
                    'difficulty_distribution' => [
                        'easy' => 40,
                        'medium' => 40,
                        'hard' => 20
                    ],
                ]);
            }
            
            $dataStructures = $subjects->firstWhere('id', 2);
            if ($dataStructures) {
                PaperSubject::create([
                    'paper_id' => $programmingPaper->id,
                    'subject_id' => $dataStructures->id,
                    'percentage' => 40,
                    'number_of_questions' => 10,
                    'difficulty_distribution' => [
                        'easy' => 30,
                        'medium' => 50,
                        'hard' => 20
                    ],
                ]);
            }
        }
        
        // For the second paper, add Data Structures and Algorithms
        $dsPaper = $papers->firstWhere('title', 'Data Structures and Algorithms');
        if ($dsPaper) {
            $dataStructures = $subjects->firstWhere('id', 2);
            if ($dataStructures) {
                PaperSubject::create([
                    'paper_id' => $dsPaper->id,
                    'subject_id' => $dataStructures->id,
                    'percentage' => 100,
                    'number_of_questions' => 25,
                    'difficulty_distribution' => [
                        'easy' => 20,
                        'medium' => 50,
                        'hard' => 30
                    ],
                ]);
            }
        }
        
        // For the third paper, add Database Systems
        $dbPaper = $papers->firstWhere('title', 'Database Management Systems');
        if ($dbPaper) {
            $dbSystems = $subjects->firstWhere('id', 3);
            if ($dbSystems) {
                PaperSubject::create([
                    'paper_id' => $dbPaper->id,
                    'subject_id' => $dbSystems->id,
                    'percentage' => 100,
                    'number_of_questions' => 20,
                    'difficulty_distribution' => [
                        'easy' => 30,
                        'medium' => 50,
                        'hard' => 20
                    ],
                ]);
            }
        }
        
        // For the fourth paper (Mathematics Test), add Calculus
        $mathsPaper = $papers->firstWhere('title', 'Mathematics Test');
        if ($mathsPaper) {
            $calculus = $subjects->firstWhere('id', 4);
            if ($calculus) {
                PaperSubject::create([
                    'paper_id' => $mathsPaper->id,
                    'subject_id' => $calculus->id,
                    'percentage' => 100,
                    'number_of_questions' => 20,
                    'difficulty_distribution' => [
                        'easy' => 30,
                        'medium' => 50,
                        'hard' => 20
                    ],
                ]);
            }
        }
        
        // For the fifth paper (Physics Test), add Physics
        $physicsPaper = $papers->firstWhere('title', 'Physics Test');
        if ($physicsPaper) {
            $physics = $subjects->firstWhere('id', 5);
            if ($physics) {
                PaperSubject::create([
                    'paper_id' => $physicsPaper->id,
                    'subject_id' => $physics->id,
                    'percentage' => 100,
                    'number_of_questions' => 15,
                    'difficulty_distribution' => [
                        'easy' => 25,
                        'medium' => 50,
                        'hard' => 25
                    ],
                ]);
            }
        }
        
        // For the sixth paper (Chemistry Test), add Chemistry
        $chemistryPaper = $papers->firstWhere('title', 'Chemistry Test');
        if ($chemistryPaper) {
            $chemistry = $subjects->firstWhere('id', 6);
            if ($chemistry) {
                PaperSubject::create([
                    'paper_id' => $chemistryPaper->id,
                    'subject_id' => $chemistry->id,
                    'percentage' => 100,
                    'number_of_questions' => 15,
                    'difficulty_distribution' => [
                        'easy' => 30,
                        'medium' => 45,
                        'hard' => 25
                    ],
                ]);
            }
        }
    }
}
