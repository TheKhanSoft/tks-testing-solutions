<?php

namespace App\Support;

class ImportFields
{
    public static array $questionFields = [
        'question_text' => 'Question Text',
        'subject_id' => 'Subject ID',
        'question_type_id' => 'Question Type ID',
        'difficulty_level' => 'Difficulty Level',
        'marks' => 'Marks',
        'negative_marks' => 'Negative Marks',
        'status' => 'Status',
        'explanation' => 'Explanation',
        'options' => 'Options (Format: text|is_correct|order separated by ||)'
    ];

    public static array $sampleRow = [
        'What is 2 + 2?',
        '1', // subject_id
        '1', // question_type_id (assuming 1 is multiple choice)
        'easy',
        '1',
        '0',
        'active',
        'Basic addition problem',
        'Three|0|1||Four|1|2||Five|0|3||Six|0|4' // options with text|is_correct|order format
    ];
}
