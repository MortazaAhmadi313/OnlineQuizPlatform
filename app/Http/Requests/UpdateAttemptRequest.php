<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
            'quiz_id' => 'sometimes|integer|exists:quizzes,id',
            'score' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:in_progress,completed,expired',
            'started_at' => 'sometimes|date',
            'completed_at' => 'sometimes|nullable|date|after_or_equal:started_at',
        ];
    }
}
