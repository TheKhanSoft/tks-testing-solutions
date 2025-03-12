<?php

namespace Database\Seeders;

use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

class QuestionOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // CPU question options
            [
                'question_id' => 1,
                'text' => 'Central Processing Unit',
                'is_correct' => true,
            ],
            [
                'question_id' => 1,
                'text' => 'Computer Personal Unit',
                'is_correct' => false,
            ],
            [
                'question_id' => 1,
                'text' => 'Central Program Unit',
                'is_correct' => false,
            ],
            [
                'question_id' => 1,
                'text' => 'Control Processing Unit',
                'is_correct' => false,
            ],
            
            // Programming language question options
            [
                'question_id' => 2,
                'text' => 'Java',
                'is_correct' => false,
            ],
            [
                'question_id' => 2,
                'text' => 'HTML',
                'is_correct' => true, // HTML is a markup language
            ],
            [
                'question_id' => 2,
                'text' => 'Python',
                'is_correct' => false,
            ],
            [
                'question_id' => 2,
                'text' => 'C++',
                'is_correct' => false,
            ],
            
            // Python question options (True/False)
            [
                'question_id' => 3,
                'text' => 'True',
                'is_correct' => false,
            ],
            [
                'question_id' => 3,
                'text' => 'False',
                'is_correct' => true, // Python is interpreted
            ],
            
            // Binary search time complexity
            [
                'question_id' => 4,
                'text' => 'O(n)',
                'is_correct' => false,
            ],
            [
                'question_id' => 4,
                'text' => 'O(log n)',
                'is_correct' => true,
            ],
            [
                'question_id' => 4,
                'text' => 'O(n²)',
                'is_correct' => false,
            ],
            [
                'question_id' => 4,
                'text' => 'O(n log n)',
                'is_correct' => false,
            ],
            
            // LIFO data structure
            [
                'question_id' => 5,
                'text' => 'Queue',
                'is_correct' => false,
            ],
            [
                'question_id' => 5,
                'text' => 'Stack',
                'is_correct' => true,
            ],
            [
                'question_id' => 5,
                'text' => 'Linked List',
                'is_correct' => false,
            ],
            [
                'question_id' => 5,
                'text' => 'Tree',
                'is_correct' => false,
            ],
            
            // SQL keyword
            [
                'question_id' => 6,
                'text' => 'GET',
                'is_correct' => false,
            ],
            [
                'question_id' => 6,
                'text' => 'EXTRACT',
                'is_correct' => false,
            ],
            [
                'question_id' => 6,
                'text' => 'SELECT',
                'is_correct' => true,
            ],
            [
                'question_id' => 6,
                'text' => 'FIND',
                'is_correct' => false,
            ],
            
            // Derivative of sin(x)
            [
                'question_id' => 8,
                'text' => 'cos(x)',
                'is_correct' => true,
            ],
            [
                'question_id' => 8,
                'text' => '-sin(x)',
                'is_correct' => false,
            ],
            [
                'question_id' => 8,
                'text' => '-cos(x)',
                'is_correct' => false,
            ],
            [
                'question_id' => 8,
                'text' => 'tan(x)',
                'is_correct' => false,
            ],
            
            // Battle of Badr
            [
                'question_id' => 11,
                'text' => '622 CE',
                'is_correct' => false,
            ],
            [
                'question_id' => 11,
                'text' => '624 CE',
                'is_correct' => true,
            ],
            [
                'question_id' => 11,
                'text' => '627 CE',
                'is_correct' => false,
            ],
            [
                'question_id' => 11,
                'text' => '630 CE',
                'is_correct' => false,
            ],
        ];

        foreach ($options as $option) {
            QuestionOption::create($option);
        }
    }
}
