<?php

namespace App\Http\Requests;

use App\Models\Attempt;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'student') {
            return false;
        }

        $attempt = Attempt::find($this->input('attempt_id'));
        $question = Question::find($this->input('question_id'));

        if (! $attempt || ! $question) {
            return false;
        }

        if (
            (int) $attempt->user_id !== (int) $user->id
            || $attempt->status !== 'in_progress'
            || (int) $attempt->quiz_id !== (int) $question->quiz_id
        ) {
            return false;
        }

        if ($attempt->started_at === null) {
            return false;
        }

        $attempt->loadMissing('quiz');

        $deadline = $attempt->started_at->copy()
            ->addMinutes($attempt->quiz->duration);

        return now()->lt($deadline);
    }

    public function rules(): array
    {
        $question = Question::find($this->input('question_id'));

        $rules = [
            'attempt_id' => [
                'required',
                'integer',
                'exists:attempts,id',
            ],
            'question_id' => [
                'required',
                'integer',
                'exists:questions,id',
                Rule::unique('answers', 'question_id')
                    ->where('attempt_id', $this->input('attempt_id')),
            ],
            'option_id' => 'prohibited',
            'answer_text' => 'prohibited',
            'awarded_points' => 'prohibited',
        ];

        if (! $question) {
            return $rules;
        }

        if (in_array($question->type, ['multiple_choice', 'true_false'], true)) {
            $rules['option_id'] = [
                'required',
                'integer',
                Rule::exists('options', 'id')
                    ->where('question_id', $question->id),
            ];
        } elseif (in_array($question->type, ['short_answer', 'essay'], true)) {
            $rules['answer_text'] = 'required|string';
        }

        return $rules;
    }
}
