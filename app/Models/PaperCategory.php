<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaperCategory extends Model
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
     * Get the papers that belong to this category
     */
    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }

    /**
     * Get the published papers that belong to this category
     */
    public function publishedPapers()
    {
        return $this->papers()->where('status', 'published');
    }
}