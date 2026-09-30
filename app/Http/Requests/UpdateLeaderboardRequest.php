<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaderboardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quiz_id' => 'sometimes|integer|exists:quizzes,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'attempt_id' => 'sometimes|integer|exists:attempts,id',
            'score' => 'sometimes|numeric|min:0',
            'rank' => 'sometimes|integer|min:1',
        ];
    }
}
