<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaperQuestion extends Model // Pivot table for Paper-Question relationship with order
{
    use HasFactory;
    protected $table = 'paper_questions';
    protected $fillable = ['paper_id', 'question_id', 'order_index']; // order_index for shuffled order
}