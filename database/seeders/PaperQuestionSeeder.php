<?php

namespace Database\Seeders;

use App\Models\PaperQuestion;
use Illuminate\Database\Seeder;

class PaperQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $paperQuestions = [
            // Computer Science Entry Test Questions
            [
                'paper_id' => 1,
                'question_id' => 1, // What does CPU stand for?
                'order_index' => 1,
            ],
            [
                'paper_id' => 1,
                'question_id' => 2, // Which is not a programming language
                'order_index' => 2,
            ],
            [
                'paper_id' => 3, // Python is compiled
                'question_id' => 3,
                'order_index' => 3,
            ],
            [
                'paper_id' => 1,
                'question_id' => 4, // Binary search complexity
                'order_index' => 4,
            ],
            [
                'paper_id' => 1,
                'question_id' => 5, // LIFO data structure
                'order_index' => 5,
            ],
            [
                'paper_id' => 1,
                'question_id' => 8, // Derivative of sin(x)
                'order_index' => 6,
            ],
            [
                'paper_id' => 1,
                'question_id' => 9, // Integral of e^x
                'order_index' => 7,
            ],
            
            // GAT General Questions
            [
                'paper_id' => 2,
                'question_id' => 8, // Derivative
                'order_index' => 1,
            ],
            [
                'paper_id' => 2,
                'question_id' => 9, // Integral 
                'order_index' => 2,
            ],
            [
                'paper_id' => 2,
                'question_id' => 10, // Essay on education
                'order_index' => 3,
            ],
            [
                'paper_id' => 2,
                'question_id' => 11, // Battle of Badr
                'order_index' => 4,
            ],
            
            // Programming Midterm Questions
            [
                'paper_id' => 3,
                'question_id' => 1,
                'order_index' => 1,
            ],
            [
                'paper_id' => 3,
                'question_id' => 2,
                'order_index' => 2,
            ],
            [
                'paper_id' => 3,
                'question_id' => 3,
                'order_index' => 3,
            ],
            
            // Database Systems Final
            [
                'paper_id' => 4,
                'question_id' => 6, // SQL keyword
                'order_index' => 1,
            ],
            [
                'paper_id' => 4,
                'question_id' => 7, // Normalization
                'order_index' => 2,
            ],
        ];

        foreach ($paperQuestions as $question) {
            PaperQuestion::create($question);
        }
    }
}
