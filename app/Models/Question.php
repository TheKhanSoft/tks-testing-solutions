<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'question_type_id',
        'question_text',
        'correct_answer', // Store correct answer (could be text or option ID for MCQs)
        'marks',
        // Add fields specific to question types (e.g., options for MCQs)
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }

    public function paperQuestions()
    {
        return $this->hasMany(PaperQuestion::class);
    }

    // For MCQ specific options - you can use a separate related model or JSON column
    // Example using a separate model:
    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }
}