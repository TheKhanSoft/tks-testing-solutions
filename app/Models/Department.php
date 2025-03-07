<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class Department extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = ['name', 'description'];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'department_subject');
    }

    public function facultyMembers()
    {
        return $this->hasMany(FacultyMember::class);
    }

    // Scopes
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', "%{$searchTerm}%")
                     ->orWhere('description', 'like', "%{$searchTerm}%");
    }
}