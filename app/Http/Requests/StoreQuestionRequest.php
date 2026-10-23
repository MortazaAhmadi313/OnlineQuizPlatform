<?php

namespace App\Http\Requests;

use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'teacher') {
            return false;
        }

        return Quiz::where('id', $this->input('quiz_id'))
            ->where('created_by', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'question_text' => 'required|string',
            'type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'points' => 'sometimes|integer|min:1',
            'difficulty' => 'sometimes|in:easy,medium,hard',
        ];
    }
}
