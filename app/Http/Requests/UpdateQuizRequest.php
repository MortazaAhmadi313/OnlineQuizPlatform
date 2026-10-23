<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && in_array($this->user()->role, ['admin', 'teacher'], true);
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|integer|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'duration' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:draft,published,closed',
        ];
    }
}
