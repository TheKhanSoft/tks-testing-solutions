<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the questions for this question type
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Determine if this question type is multiple choice
     */
    public function isMultipleChoice(): bool
    {
        return $this->id === 1;
    }

    /**
     * Determine if this question type is multiple select
     */
    public function isMultipleSelect(): bool
    {
        return $this->id === 2;
    }

    /**
     * Determine if this question type is true/false
     */
    public function isTrueFalse(): bool
    {
        return $this->id === 3;
    }

    /**
     * Determine if this question type is short answer
     */
    public function isShortAnswer(): bool
    {
        return $this->id === 4;
    }

    /**
     * Determine if this question type is essay
     */
    public function isEssay(): bool
    {
        return $this->id === 5;
    }

    /**
     * Determine if this question type is fill in the blank
     */
    public function isFillInTheBlank(): bool
    {
        return $this->id === 6;
    }

    /**
     * Scope a query to only include multiple choice questions
     */
    public function scopeMultipleChoice($query)
    {
        return $query->where('id', 1);
    }

    /**
     * Scope a query to only include multiple select questions
     */
    public function scopeMultipleSelect($query)
    {
        return $query->where('id', 2);
    }
}