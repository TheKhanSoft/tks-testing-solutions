<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class PaperCategory extends Model // Categories for Papers (e.g., "Midterm", "Final Exam", "Practice Test")
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = ['name', 'description'];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function papers()
    {
        return $this->hasMany(Paper::class);
    }

    // Scopes
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', "%{$searchTerm}%")
                     ->orWhere('description', 'like', "%{$searchTerm}%");
    }
}