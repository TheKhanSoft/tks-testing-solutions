<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class QuestionResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'test_response_id',
        'question_id',
        'answer_text',
        'score',
        'max_score',
        'is_correct',
        'graded_by',
        'graded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'float',
        'max_score' => 'float',
        'is_correct' => 'boolean',
        'graded_at' => 'datetime',
    ];

    /**
     * Get the test response that this answer belongs to
     */
    public function testResponse(): BelongsTo
    {
        return $this->belongsTo(TestResponse::class);
    }

    /**
     * Get the question that this response is for
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the grader of this response (if applicable)
     */
    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the selected options for multiple choice/multiple select questions
     */
    public function selectedOptions(): BelongsToMany
    {
        return $this->belongsToMany(QuestionOption::class, 'question_response_options');
    }

    /**
     * Scope a query to only include graded responses
     */
    public function scopeGraded($query)
    {
        return $query->whereNotNull('graded_at');
    }

    /**
     * Scope a query to only include ungraded responses
     */
    public function scopeUngraded($query)
    {
        return $query->whereNull('graded_at');
    }
}
