<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration' => $this->duration,
            'status' => $this->status,
            'category_id' => $this->category_id,
            'created_by' => $this->created_by,
            'questions_count' => $this->whenCounted('questions'),
        ];
    }
}