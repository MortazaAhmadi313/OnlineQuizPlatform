<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
            'quiz_id' => 'sometimes|integer|exists:quizzes,id',
            'score' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:in_progress,completed,expired',
            'started_at' => 'sometimes|date',
            'completed_at' => 'sometimes|nullable|date',
        ];
    }
}
