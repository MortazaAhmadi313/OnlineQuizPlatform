<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $answer = $this->route('answer');

        if (
            ! $user
            || $user->role !== 'student'
            || ! $answer
        ) {
            return false;
        }

        $attempt = $answer->attempt;

        if (
            ! $attempt
            || (int) $attempt->user_id !== (int) $user->id
            || $attempt->status !== 'in_progress'
            || ! $attempt->started_at
        ) {
            return false;
        }

        $attempt->loadMissing('quiz');

        $deadline = $attempt->started_at->copy()
            ->addMinutes($attempt->quiz->duration);

        return now()->lt($deadline);
    }

    public function rules(): array
    {
        $answer = $this->route('answer');
        $question = $answer?->question;

        $rules = [
            'attempt_id' => 'prohibited',
            'question_id' => 'prohibited',
            'awarded_points' => 'prohibited',
            'option_id' => 'prohibited',
            'answer_text' => 'prohibited',
        ];

        if (! $question) {
            return $rules;
        }

        if (in_array($question->type, ['multiple_choice', 'true_false'], true)) {
            $rules['option_id'] = [
                'sometimes',
                'required',
                'integer',
                Rule::exists('options', 'id')
                    ->where('question_id', $question->id),
            ];
        } elseif (in_array($question->type, ['short_answer', 'essay'], true)) {
            $rules['answer_text'] = 'sometimes|required|string';
        }

        return $rules;
    }
}
