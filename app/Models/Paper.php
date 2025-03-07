<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class Paper extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = [
        'subject_id',
        'paper_category_id',
        'name',
        'description',
        'duration_minutes',
        'total_marks',
        'instructions', // Added instructions
        'is_published',  // Added is_published status
    ];
    protected $casts = [
        'is_published' => 'boolean', // Cast is_published to boolean
    ];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function paperCategory()
    {
        return $this->belongsTo(PaperCategory::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'paper_question')->withPivot('order_index'); // Order index for shuffled order
    }

    public function userCategories()
    {
        return $this->belongsToMany(UserCategory::class, 'paper_user_category');
    }

    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOfCategory($query, $paperCategoryId)
    {
        return $query->where('paper_category_id', $paperCategoryId);
    }

    public function scopeOfSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }


    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', "%{$searchTerm}%")
                     ->orWhere('description', 'like', "%{$searchTerm}%")
                     ->orWhereHas('subject', function ($q) use ($searchTerm) {
                         $q->where('name', 'like', "%{$searchTerm}%");
                     })
                     ->orWhereHas('paperCategory', function ($q) use ($searchTerm) {
                         $q->where('name', 'like', "%{$searchTerm}%");
                     });
    }

    // Eager Loading
    protected $with = ['subject', 'paperCategory']; // Eager load subject and paperCategory by default
}