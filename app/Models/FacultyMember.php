<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Use Laravel's built-in User model as base
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyMember extends Authenticatable // Extending Laravel's User for Faculty
{
    use HasFactory, SoftDeletes; //  Notifiable, Use SoftDeletes and Notifiable traits

    protected $fillable = ['name', 'email', 'password', 'department_id', 'profile_picture', 'designation']; // Added profile_picture, designation
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean', // Added is_active cast
    ];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_faculty');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', "%{$searchTerm}%")
                     ->orWhere('email', 'like', "%{$searchTerm}%");
    }

    // Eager Loading
    protected $with = ['department']; // Eager load department by default
}