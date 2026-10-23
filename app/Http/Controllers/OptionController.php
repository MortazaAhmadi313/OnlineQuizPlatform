<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Http\Resources\OptionResource;
use App\Models\Option;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Option::query()->with('question.quiz');

        if ($user->role === 'teacher') {
            $query->whereHas('question.quiz', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        } elseif ($user->role === 'student') {
            $query->whereHas('question.quiz', function ($q) {
                $q->where('status', 'published');
            })->whereHas('question', function ($q) {
                $q->whereIn('type', ['multiple_choice', 'true_false']);
            });
        }

        if ($request->filled('question_id')) {
            $query->where('question_id', $request->query('question_id'));
        }

        if ($request->filled('search')) {
            $query->where('option_text', 'like', '%' . $request->query('search') . '%');
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        if (! in_array($sort, ['created_at'], true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));

        return OptionResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(StoreOptionRequest $request)
    {
        $option = Option::create($request->validated());

        return (new OptionResource($option))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Option $option, Request $request)
    {
        $this->authorizeOptionAccess($option, $request->user());

        return new OptionResource($option);
    }

    public function update(UpdateOptionRequest $request, Option $option)
    {
        $option->update($request->validated());

        return new OptionResource($option);
    }

    public function destroy(Option $option, Request $request)
    {
        $user = $request->user();
        $quiz = $option->question?->quiz;

        abort_unless(
            $user->role === 'admin'
            || (
                $user->role === 'teacher'
                && $quiz
                && (int) $quiz->created_by === (int) $user->id
            ),
            403,
            'You are not allowed to delete this option.'
        );

        $option->delete();

        return response()->noContent();
    }

    private function authorizeOptionAccess(Option $option, $user): void
    {
        if ($user->role === 'admin') {
            return;
        }

        $question = $option->question;
        $quiz = $question?->quiz;

        if (
            $user->role === 'teacher'
            && $quiz
            && (int) $quiz->created_by === (int) $user->id
        ) {
            return;
        }

        if (
            $user->role === 'student'
            && $quiz
            && $quiz->status === 'published'
            && in_array($question->type, ['multiple_choice', 'true_false'], true)
        ) {
            return;
        }

        abort(403, 'You are not allowed to access this option.');
    }
}
