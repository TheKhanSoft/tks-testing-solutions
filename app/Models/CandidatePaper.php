<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidatePaper extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'test_attempt_id',
        'question_id',
        'candidate_answer',
        'is_correct',
        'marks_obtained',
        'start_time',
        'end_time',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'marks_obtained' => 'integer',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the test attempt that this response belongs to
     */
    public function testAttempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class);
    }

    /**
     * Get the question that this response is for
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Scope a query to only include correct answers
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope a query to only include incorrect answers
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope a query to only include attempted questions
     */
    public function scopeAttempted($query)
    {
        return $query->where('status', 'attempted');
    }

    /**
     * Scope a query to only include skipped questions
     */
    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    /**
     * Scope a query to only include unattempted questions
     */
    public function scopeUnattempted($query)
    {
        return $query->where('status', 'unattempted');
    }

    /**
     * Check if the question was attempted
     */
    public function isAttempted(): bool
    {
        return $this->status === 'attempted';
    }

    /**
     * Calculate time spent on this question in seconds
     */
    public function timeSpent()
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        return $this->start_time->diffInSeconds($this->end_time);
    }
}
