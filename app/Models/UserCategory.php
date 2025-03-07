<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class UserCategory extends Model // Categories for Users (e.g., "Undergraduate", "Graduate", "Professional")
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = ['name', 'description'];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function papers()
    {
        return $this->belongsToMany(Paper::class, 'paper_user_category');
    }

    public function users()
    {
        return $this->hasMany(User::class); // Assuming you have a User model
    }

    // Scopes
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('name', 'like', "%{$searchTerm}%")
                     ->orWhere('description', 'like', "%{$searchTerm}%");
    }
}