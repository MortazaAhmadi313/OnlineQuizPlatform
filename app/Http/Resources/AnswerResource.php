<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        $attempt = $this->attempt;

        $canViewScore = $user
            && in_array($user->role, ['admin', 'teacher'], true);

        return [
            'id' => $this->id,
            'attempt_id' => $this->attempt_id,
            'question_id' => $this->question_id,
            'option_id' => $this->option_id,
            'answer_text' => $this->answer_text,
            'awarded_points' => $canViewScore
                ? $this->awarded_points
                : null,
        ];
    }
}
