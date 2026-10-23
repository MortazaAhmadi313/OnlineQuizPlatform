<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100|unique:categories,name,' . $this->category->id,
            'description' => 'sometimes|nullable|string',
        ];
    }
}
