<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $option = $this->route('option');

        if (! $user || ! $option) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role !== 'teacher') {
            return false;
        }

        $question = $option->question;

        if (
            ! $question
            || ! $question->quiz
            || (int) $question->quiz->created_by !== (int) $user->id
        ) {
            return false;
        }

        if ($this->filled('question_id')) {
            $newQuestion = Question::with('quiz')->find($this->input('question_id'));

            return $newQuestion
                && $newQuestion->quiz
                && $newQuestion->type !== 'essay'
                && (int) $newQuestion->quiz->created_by === (int) $user->id;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => [
                'sometimes',
                'integer',
                'exists:questions,id',
                function ($attribute, $value, $fail) {
                    $question = Question::find($value);

                    if ($question && $question->type === 'essay') {
                        $fail('Essay questions cannot have options.');
                    }
                },
            ],
            'option_text' => 'sometimes|string|max:500',
            'is_correct' => 'sometimes|boolean',
        ];
    }
}
