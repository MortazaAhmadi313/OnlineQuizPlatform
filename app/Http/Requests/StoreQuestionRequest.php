<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
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
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'question_text' => 'required|string',
            'points' => 'sometimes|integer|min:1',
            'difficulty' => 'sometimes|in:easy,medium,hard',
        ];
    }
}
