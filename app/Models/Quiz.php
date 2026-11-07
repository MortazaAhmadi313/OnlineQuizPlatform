<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'created_by',
        'title',
        'description',
        'duration',
        'status',
    ];

    public function scopeSearch($query, ?string $search)
    {
        if (blank($search)) {
            return $query;
        }

        return $query->where('title', 'like', '%' . $search . '%');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function leaderboards(): HasMany
    {
        return $this->hasMany(Leaderboard::class);
    }

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
        ];
    }
}
