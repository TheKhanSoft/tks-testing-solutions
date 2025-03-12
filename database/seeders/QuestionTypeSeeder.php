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
                'description' => 'A question with multiple options where only one is correct',
            ],
            [
                'name' => 'Multiple Response Question',
                'description' => 'A question with multiple options where multiple responses can be correct',
            ],
            [
                'name' => 'True/False',
                'description' => 'A statement that must be marked as either true or false',
            ],
            [
                'name' => 'Short Answer',
                'description' => 'A question requiring a brief text response',
            ],
            [
                'name' => 'Essay',
                'description' => 'A question requiring an extended text response',
            ],
            [
                'name' => 'Fill in the Blank',
                'description' => 'A statement with missing words that must be filled in',
            ],
        ];

        foreach ($questionTypes as $type) {
            QuestionType::create($type);
        }
    }
}
