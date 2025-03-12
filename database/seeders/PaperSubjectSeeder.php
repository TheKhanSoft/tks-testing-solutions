<?php

namespace Database\Seeders;

use App\Models\PaperSubject;
use Illuminate\Database\Seeder;

class PaperSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $paperSubjects = [
            // Computer Science Entry Test
            [
                'paper_id' => 1, // Computer Science Entry Test
                'subject_id' => 1, // Programming Fundamentals
                'percentage' => 40.0,
                'number_of_questions' => 20,
                'difficulty_distribution' => json_encode([
                    'easy' => 40,
                    'medium' => 40,
                    'hard' => 20
                ]),
            ],
            [
                'paper_id' => 1, // Computer Science Entry Test
                'subject_id' => 4, // Calculus
                'percentage' => 30.0,
                'number_of_questions' => 15,
                'difficulty_distribution' => json_encode([
                    'easy' => 30,
                    'medium' => 50,
                    'hard' => 20
                ]),
            ],
            [
                'paper_id' => 1, // Computer Science Entry Test
                'subject_id' => 8, // English Composition
                'percentage' => 30.0,
                'number_of_questions' => 15,
                'difficulty_distribution' => json_encode([
                    'easy' => 40,
                    'medium' => 40,
                    'hard' => 20
                ]),
            ],
            
            // GAT General
            [
                'paper_id' => 2, // GAT General
                'subject_id' => 4, // Calculus
                'percentage' => 30.0,
                'number_of_questions' => 20,
                'difficulty_distribution' => json_encode([
                    'easy' => 30,
                    'medium' => 50,
                    'hard' => 20
                ]),
            ],
            [
                'paper_id' => 2, // GAT General
                'subject_id' => 8, // English Composition
                'percentage' => 40.0,
                'number_of_questions' => 25,
                'difficulty_distribution' => json_encode([
                    'easy' => 30,
                    'medium' => 50,
                    'hard' => 20
                ]),
            ],
            [
                'paper_id' => 2, // GAT General
                'subject_id' => 9, // Islamic History
                'percentage' => 30.0,
                'number_of_questions' => 15,
                'difficulty_distribution' => json_encode([
                    'easy' => 40,
                    'medium' => 40,
                    'hard' => 20
                ]),
            ],
            
            // Programming Midterm
            [
                'paper_id' => 3, // Programming Midterm
                'subject_id' => 1, // Programming Fundamentals
                'percentage' => 100.0,
                'number_of_questions' => 30,
                'difficulty_distribution' => json_encode([
                    'easy' => 30,
                    'medium' => 50,
                    'hard' => 20
                ]),
            ],
        ];

        foreach ($paperSubjects as $subject) {
            PaperSubject::create($subject);
        }
    }
}
