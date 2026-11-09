<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canViewCorrectAnswer = in_array(
            $request->user()?->role,
            ['admin', 'teacher'],
            true
        );

        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'option_text' => $this->option_text,
            'is_correct' => $canViewCorrectAnswer
                ? $this->is_correct
                : null,
        ];
    }
}