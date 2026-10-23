<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Question::with('options');

        if ($user->role === 'teacher') {
            $query->whereHas('quiz', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        } elseif ($user->role === 'student') {
            $query->whereHas('quiz', function ($q) {
                $q->where('status', 'published');
            });
        }

        return QuestionResource::collection(
            $query->latest()->paginate(15)
        );
    }

    public function store(StoreQuestionRequest $request)
    {
        $question = Question::create($request->validated());

        return (new QuestionResource($question))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Question $question)
    {
        $this->authorizeQuestionAccess($request, $question);

        return new QuestionResource($question->load('options'));
    }

    public function update(
        UpdateQuestionRequest $request,
        Question $question
    ) {
        $question->update($request->validated());

        return new QuestionResource($question);
    }

    public function destroy(Request $request, Question $question)
    {
        $this->authorizeQuestionManagement($request, $question);

        $question->delete();

        return response()->noContent();
    }

    private function authorizeQuestionAccess(
        Request $request,
        Question $question
    ): void {
        $user = $request->user();

        if ($user->role === 'admin') {
            return;
        }

        $quiz = Quiz::findOrFail($question->quiz_id);

        if (
            $user->role === 'teacher'
            && (int) $quiz->created_by === (int) $user->id
        ) {
            return;
        }

        if (
            $user->role === 'student'
            && $quiz->status === 'published'
        ) {
            return;
        }

        abort(403, 'You are not allowed to access this question.');
    }

    private function authorizeQuestionManagement(
        Request $request,
        Question $question
    ): void {
        $user = $request->user();

        if ($user->role === 'admin') {
            return;
        }

        $quiz = Quiz::findOrFail($question->quiz_id);

        abort_unless(
            $user->role === 'teacher'
            && (int) $quiz->created_by === (int) $user->id,
            403,
            'You are not allowed to manage this question.'
        );
    }
}
