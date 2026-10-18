<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'user_id' => $this->user_id,
            'attempt_id' => $this->attempt_id,
            'score' => $this->score,
            'rank' => $this->rank,
        ];
    }
}