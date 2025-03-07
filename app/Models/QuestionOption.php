<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class QuestionOption extends Model // For MCQ Options
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = ['question_id', 'option_text', 'is_correct']; // is_correct for MCQs
    protected $dates = ['deleted_at']; // To enable soft deletes


    // Relationships
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Scopes
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }
}