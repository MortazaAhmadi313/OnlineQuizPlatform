<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;

class StoreOptionRequest extends FormRequest
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

        $question = Question::with('quiz')->find($this->input('question_id'));

        return $question
            && $question->quiz
            && (int) $question->quiz->created_by === (int) $user->id;
    }

    public function rules(): array
    {
        return [
            'question_id' => [
                'required',
                'integer',
                'exists:questions,id',
                function ($attribute, $value, $fail) {
                    $question = Question::find($value);

                    if ($question && $question->type === 'essay') {
                        $fail('Essay questions cannot have options.');
                    }
                },
            ],
            'option_text' => 'required|string|max:500',
            'is_correct' => 'sometimes|boolean',
        ];
    }
}
