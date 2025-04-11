<?php

namespace Database\Seeders;

use App\Models\QuestionType;
use Illuminate\Database\Seeder;

class QuestionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $questionTypes = [
            [
                'name' => 'Multiple Choice Question',
                'short_name' => 'MCQ',
                'description' => 'A question with multiple options where only one is correct',
            ],
            [
                'name' => 'Multiple Response Question',
                'short_name' => 'MRQ',
                'description' => 'A question with multiple options where multiple responses can be correct',
            ],
            [
                'name' => 'True/False',
                'short_name' => 'T/F',
                'description' => 'A statement that must be marked as either true or false',
            ],
            [
                'name' => 'Short Answer',
                'short_name' => 'SA',
                'description' => 'A question requiring a brief text response',
            ],
            [
                'name' => 'Essay',
                'short_name' => 'ESSAY',
                'description' => 'A question requiring an extended text response',
            ],
            [
                'name' => 'Fill in the Blank',
                'short_name' => 'FIB',
                'description' => 'A statement with missing words that must be filled in',
            ],
        ];

        foreach ($questionTypes as $type) {
            QuestionType::create($type);
        }
    }
}
