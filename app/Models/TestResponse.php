<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'test_id',
        'user_id',
        'status',
        'score',
        'total_marks',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'float',
        'total_marks' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the test for this response
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    /**
     * Get the user who took the test
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all of the answers for this test response
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionResponse::class);
    }

    /**
     * Calculate the percentage score for this test response
     */
    public function getPercentageAttribute(): float
    {
        return $this->total_marks > 0 ? ($this->score / $this->total_marks) * 100 : 0;
    }

    /**
     * Determine if this test response is a pass
     */
    public function getIsPassAttribute(): bool
    {
        return $this->percentage >= $this->test->passing_percentage;
    }

    /**
     * Scope a query to only include completed test responses
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }
}
