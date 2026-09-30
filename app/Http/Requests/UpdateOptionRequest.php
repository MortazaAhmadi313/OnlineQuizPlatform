<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOptionRequest extends FormRequest
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
            'question_id' => 'sometimes|integer|exists:questions,id',
            'option_text' => 'sometimes|string|max:500',
            'is_correct' => 'sometimes|boolean',
        ];
    }
}
