<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = ['name', 'description', 'department_id']; // department_id might be managed via pivot
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_subject');
    }

    public function facultyMembers()
    {
        return $this->belongsToMany(FacultyMember::class, 'subject_faculty');
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

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

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->whereHas('departments', function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }
}