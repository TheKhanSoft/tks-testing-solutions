<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class Answer extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = [
        'test_attempt_id',
        'question_id',
        'user_answer', // Store user's answer (could be text or option ID)
        'is_correct',   // Boolean, calculated after submission
        'marks_obtained', // Calculated marks
        'time_spent_seconds', // Added time_spent_seconds
    ];
    protected $casts = [
        'is_correct' => 'boolean', // Cast is_correct to boolean
    ];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function testAttempt()
    {
        return $this->belongsTo(TestAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Scopes
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    public function scopeForTestAttempt($query, $testAttemptId)
    {
        return $query->where('test_attempt_id', $testAttemptId);
    }

    public function scopeForQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    // Eager Loading
    protected $with = ['question']; // Eager load question by default
}