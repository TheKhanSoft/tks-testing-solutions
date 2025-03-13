<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Get the questions for this subject
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Get the easy questions for this subject
     */
    public function easyQuestions()
    {
        return $this->questions()->where('difficulty_level', 'easy');
    }

    /**
     * Get the medium questions for this subject
     */
    public function mediumQuestions()
    {
        return $this->questions()->where('difficulty_level', 'medium');
    }

    /**
     * Get the hard questions for this subject
     */
    public function hardQuestions()
    {
        return $this->questions()->where('difficulty_level', 'hard');
    }

    /**
     * Get questions by type
     */
    public function questionsByType($typeId)
    {
        return $this->questions()->where('question_type_id', $typeId);
    }

    /**
     * Get MCQ questions for this subject
     */
    public function mcqQuestions()
    {
        return $this->questionsByType(1);
    }

    /**
     * Get the active questions for this subject
     */
    public function activeQuestions()
    {
        return $this->questions()->where('status', 'active');
    }

    /**
     * Get the paper subjects entries for this subject
     */
    public function paperSubjects(): HasMany
    {
        return $this->hasMany(PaperSubject::class);
    }

    /**
     * Get all papers that include this subject
     */
    public function papers(): BelongsToMany
    {
        return $this->belongsToMany(Paper::class, 'paper_subjects')
                    ->withPivot(['percentage', 'number_of_questions', 'difficulty_distribution'])
                    ->withTimestamps();
    }
}