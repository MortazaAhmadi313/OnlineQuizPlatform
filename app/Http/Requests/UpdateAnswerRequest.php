<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAnswerRequest extends FormRequest
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
            'attempt_id' => 'sometimes|integer|exists:attempts,id',
            'question_id' => 'sometimes|integer|exists:questions,id',
            'option_id' => 'sometimes|integer|exists:options,id',
        ];
    }
}
