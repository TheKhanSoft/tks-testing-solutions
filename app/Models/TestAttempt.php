<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Added Soft Deletes

class TestAttempt extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $fillable = [
        'user_id',
        'paper_id',
        'start_time',
        'end_time',
        'score',
        'status',         // e.g., 'pending', 'in_progress', 'completed'
        'is_stopped',     // Added is_stopped for admin control
        'browser_info',   // Added browser_info
        'ip_address',     // Added ip_address
    ];
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_stopped' => 'boolean', // Cast is_stopped to boolean
    ];
    protected $dates = ['deleted_at']; // To enable soft deletes

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    // Scopes
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForPaper($query, $paperId)
    {
        return $query->where('paper_id', $paperId);
    }

    // Eager Loading
    protected $with = ['user', 'paper']; // Eager load user and paper by default
}