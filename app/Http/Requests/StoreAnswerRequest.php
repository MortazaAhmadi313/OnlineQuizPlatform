<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnswerRequest extends FormRequest
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
            'attempt_id' => 'required|integer|exists:attempts,id',
            'question_id' => 'required|integer|exists:questions,id',
            'option_id' => 'required|integer|exists:options,id',
        ];
    }
}
