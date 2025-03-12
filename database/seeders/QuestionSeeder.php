<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // Programming Fundamentals questions
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'What does the acronym "CPU" stand for?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 1, // MCQ
                'text' => 'Which of the following is not a programming language?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 1,
                'question_type_id' => 3, // True/False
                'text' => 'Python is a compiled language.',
                'difficulty_level' => 'medium',
                'marks' => 1,
            ],
            
            // Data Structures questions
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'What is the time complexity of binary search?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 2,
                'question_type_id' => 1, // MCQ
                'text' => 'Which data structure works on LIFO principle?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            
            // Database Systems questions
            [
                'subject_id' => 3,
                'question_type_id' => 1, // MCQ
                'text' => 'Which SQL keyword is used to retrieve data from a database?',
                'difficulty_level' => 'easy',
                'marks' => 1,
            ],
            [
                'subject_id' => 3,
                'question_type_id' => 4, // Short Answer
                'text' => 'Define normalization in the context of database design.',
                'difficulty_level' => 'hard',
                'marks' => 3,
            ],
            
            // Calculus questions
            [
                'subject_id' => 4,
                'question_type_id' => 1, // MCQ
                'text' => 'The derivative of sin(x) is:',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            [
                'subject_id' => 4,
                'question_type_id' => 6, // Fill in the Blank
                'text' => 'The integral of e^x is ______.',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
            
            // English Composition questions
            [
                'subject_id' => 8,
                'question_type_id' => 5, // Essay
                'text' => 'Write a short essay on the importance of education in society.',
                'difficulty_level' => 'medium',
                'marks' => 5,
            ],
            
            // Islamic History questions
            [
                'subject_id' => 9,
                'question_type_id' => 1, // MCQ
                'text' => 'In which year did the Battle of Badr take place?',
                'difficulty_level' => 'medium',
                'marks' => 2,
            ],
        ];

        foreach ($questions as $question) {
            Question::create($question);
        }
    }
}
