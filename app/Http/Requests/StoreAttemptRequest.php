<?php

namespace App\Http\Requests;

use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || $user->role !== 'student') {
            return false;
        }

        return Quiz::where('id', $this->input('quiz_id'))
            ->where('status', 'published')
            ->exists();
    }

    public function rules(): array
    {
        return [
            'quiz_id' => 'required|integer|exists:quizzes,id',
            'user_id' => 'prohibited',
            'score' => 'prohibited',
            'status' => 'prohibited',
            'started_at' => 'prohibited',
            'completed_at' => 'prohibited',
        ];
    }
}
