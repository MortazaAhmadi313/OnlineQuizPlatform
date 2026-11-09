<?php

namespace App\Http\Resources;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $hasUngradedEssays = Question::query()
            ->where('quiz_id', $this->quiz_id)
            ->where('type', 'essay')
            ->whereDoesntHave('answers', function ($query) {
                $query->where('attempt_id', $this->id)
                    ->whereNotNull('awarded_points');
            })
            ->exists();

        $finalScoreReady = $this->status !== 'in_progress'
            && ! $hasUngradedEssays;

        $canViewScore = $user
            && (
                in_array($user->role, ['admin', 'teacher'], true)
                || (
                    $user->role === 'student'
                    && $finalScoreReady
                )
            );

        $canViewStudent = $user
            && in_array($user->role, ['admin', 'teacher'], true);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            'user' => $this->when(
                $canViewStudent && $this->relationLoaded('user'),
                fn () => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ]
            ),

            'quiz_id' => $this->quiz_id,
            'score' => $canViewScore ? $this->score : null,
            'final_score_ready' => $finalScoreReady,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
        ];
    }
}