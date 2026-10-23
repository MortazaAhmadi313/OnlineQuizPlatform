<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'question_text' => $this->question_text,
            'type' => $this->type,
            'points' => $this->points,
            'difficulty' => $this->difficulty,

            'options' => OptionResource::collection(
                $this->whenLoaded('options')
            ),
        ];
    }
}
