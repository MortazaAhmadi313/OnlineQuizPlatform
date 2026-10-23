<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Leaderboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'attempt_id',
        'score',
        'rank',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'rank' => 'integer',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(Attempt::class);
    }

    public static function updateBestScore(Attempt $attempt): void
    {
        DB::transaction(function () use ($attempt) {
            $entry = self::where('quiz_id', $attempt->quiz_id)
                ->where('user_id', $attempt->user_id)
                ->lockForUpdate()
                ->first();

            if (! $entry || (float) $attempt->score > (float) $entry->score) {
                self::updateOrCreate(
                    [
                        'quiz_id' => $attempt->quiz_id,
                        'user_id' => $attempt->user_id,
                    ],
                    [
                        'attempt_id' => $attempt->id,
                        'score' => $attempt->score,
                        'rank' => 1,
                    ]
                );
            }

            $entries = self::where('quiz_id', $attempt->quiz_id)
                ->orderByDesc('score')
                ->orderBy('id')
                ->get();

            foreach ($entries as $index => $leaderboardEntry) {
                $leaderboardEntry->update([
                    'rank' => $index + 1,
                ]);
            }
        });
    }
}
