<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class Question extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = [
        'subject_id',
        'question_type_id',
        'question_text',
        'correct_answer', // Store correct answer (could be text or option ID for MCQs)
        'marks',
        'difficulty_level', // Added difficulty_level
        'hint',             // Added hint
        'explanation',      // Added explanation
    ];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
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

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // Scopes
    public function scopeOfType($query, $questionTypeId)
    {
        return $query->where('question_type_id', $questionTypeId);
    }

    public function scopeOfSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('question_text', 'like', "%{$searchTerm}%")
                     ->orWhere('explanation', 'like', "%{$searchTerm}%");
    }

    // Eager Loading
    protected $with = ['questionType', 'subject']; // Eager load questionType and subject by default
}