<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'subject_id',
        'question_type_id',
        'max_time_allowed',
        'negative_marks',
        'text',
        'description',
        'explanation',
        'image',
        'marks',
        'difficulty_level',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'marks' => 'integer',
        'max_time_allowed' => 'integer',
        'negative_marks' => 'integer',
    ];

    /**
     * Get the subject that the question belongs to
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the question type that the question belongs to
     */
    public function questionType(): BelongsTo
    {
        return $this->belongsTo(QuestionType::class);
    }

    /**
     * Get the options for this question
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * Get the papers that contain this question
     */
    public function papers(): BelongsToMany
    {
        return $this->belongsToMany(Paper::class, 'paper_questions')
            ->withPivot('order_index')
            ->withTimestamps();
    }

    /**
     * Get all candidate responses for this question
     */
    public function candidateResponses(): HasMany
    {
        return $this->hasMany(CandidatePaper::class);
    }

    /**
     * Scope a query to only include questions of a specific difficulty level
     */
    public function scopeWithDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope a query to only include active questions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include questions with specific marks
     */
    public function scopeWithMarks($query, $marks)
    {
        return $query->where('marks', $marks);
    }

    /**
     * Scope a query to only include questions of a specific type
     */
    public function scopeOfType($query, $typeId)
    {
        return $query->where('question_type_id', $typeId);
    }

    /**
     * Get the correct options for this question
     */
    public function correctOptions()
    {
        return $this->options()->where('is_correct', true);
    }

    /**
     * Check if the question is a multiple choice question
     */
    public function isMultipleChoice()
    {
        return $this->question_type_id === 1;
    }

    /**
     * Check if the question is a multiple select question
     */
    public function isMultipleSelect()
    {
        return $this->question_type_id === 2;
    }
}