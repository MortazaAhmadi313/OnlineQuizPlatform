<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
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
            'quiz_id' => 'sometimes|integer|exists:quizzes,id',
            'question_text' => 'sometimes|string',
            'points' => 'sometimes|integer|min:1',
            'difficulty' => 'sometimes|in:easy,medium,hard',
        ];
    }
}
