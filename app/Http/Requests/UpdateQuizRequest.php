<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
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
            'category_id' => 'sometimes|integer|exists:categories,id',
            'created_by' => 'sometimes|nullable|integer|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'duration' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:draft,published,closed',
        ];
    }
}
