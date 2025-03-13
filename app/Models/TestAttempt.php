<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestAttempt extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'candidate_id',
        'paper_id',
        'start_time',
        'end_time',
        'score',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'score' => 'integer',
    ];

    /**
     * Get the candidate who attempted the test
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the paper that was attempted
     */
    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    /**
     * Get all candidate papers (answers) for this attempt
     */
    public function candidatePapers(): HasMany
    {
        return $this->hasMany(CandidatePaper::class);
    }

    /**
     * Scope a query to only include completed test attempts
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include in-progress test attempts
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include pending test attempts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if the test attempt is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the test attempt is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Calculate time spent on this attempt in minutes
     */
    public function timeSpent()
    {
        if (!$this->start_time) {
            return 0;
        }

        $end = $this->end_time ?? now();
        return $this->start_time->diffInMinutes($end);
    }

    /**
     * Calculate the percentage score
     */
    public function percentageScore()
    {
        if (!$this->score || !$this->paper->total_marks) {
            return 0;
        }

        return ($this->score / $this->paper->total_marks) * 100;
    }

    /**
     * Determine if the candidate passed
     */
    public function isPassed()
    {
        return $this->percentageScore() >= $this->paper->passing_percentage;
    }
}