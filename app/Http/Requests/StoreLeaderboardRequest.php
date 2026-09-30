<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaderboardRequest extends FormRequest
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
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'user_id' => 'required|integer|exists:users,id',
            'attempt_id' => 'required|integer|exists:attempts,id',
            'score' => 'required|numeric|min:0',
            'rank' => 'required|integer|min:1',
        ];
    }
}
