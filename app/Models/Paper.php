<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paper extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'paper_category_id',
        'title',
        'description',
        'total_marks',
        'passing_marks',
        'duration_minutes',
        'settings',
        'shuffle_questions',
        'shuffle_options',
        'show_results_immediately',
        'passing_percentage',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_marks' => 'integer',
        'passing_marks' => 'integer',
        'duration_minutes' => 'integer',
        'settings' => 'json',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'show_results_immediately' => 'boolean',
        'passing_percentage' => 'integer',
    ];

    /**
     * Get the category of the paper
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PaperCategory::class, 'paper_category_id');
    }

    /**
     * Get the questions in this paper
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'paper_questions')
                    ->withPivot('order_index')
                    ->withTimestamps();
    }

    /**
     * Get the test attempts for this paper
     */
    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    /**
     * Get the subjects for this paper
     */
    public function paperSubjects(): HasMany
    {
        return $this->hasMany(PaperSubject::class);
    }

    /**
     * Get all subjects for this paper through paper_subjects
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'paper_subjects')
                    ->withPivot(['percentage', 'number_of_questions', 'difficulty_distribution'])
                    ->withTimestamps();
    }

    /**
     * Scope a query to only include published papers
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft papers
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include archived papers
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Determine if the paper is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Calculate the maximum possible score for this paper
     */
    public function calculateMaxScore(): int
    {
        return $this->total_marks;
    }

    /**
     * Check if a candidate passed the paper
     */
    public function isPassed($score): bool
    {
        return $score >= $this->passing_marks;
    }
}