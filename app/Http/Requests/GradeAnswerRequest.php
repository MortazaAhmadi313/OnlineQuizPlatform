<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $answer = $this->route('answer');

        if (
            ! $user
            || ! $answer
            || ! in_array($user->role, ['admin', 'teacher'], true)
        ) {
            return false;
        }

        $question = $answer->question;
        $quiz = $question?->quiz;
        $attempt = $answer->attempt;

        if (
            ! $question
            || $question->type !== 'essay'
            || ! $quiz
            || ! $attempt
            || $attempt->status === 'in_progress'
        ) {
            return false;
        }

        if ($user->role === 'teacher') {
            return (int) $quiz->created_by === (int) $user->id;
        }

        return true;
    }

    public function rules(): array
    {
        $answer = $this->route('answer');
        $question = $answer?->question;

        return [
            'awarded_points' => [
                'required',
                'integer',
                'min:0',
                'max:' . ($question?->points ?? 0),
            ],
        ];
    }
}
