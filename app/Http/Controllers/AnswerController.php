<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Models\Answer;
use App\Http\Resources\AnswerResource;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource
     */
    public function index(Request $request)
    {
        $query = Answer::query();

        if ($request->filled('attempt_id')) {
            $query->where('attempt_id', $request->attempt_id);
        }

        if ($request->filled('question_id')) {
            $query->where('question_id', $request->question_id);
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

        return AnswerResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(StoreAnswerRequest $request)
    {
        $answer = Answer::create($request->validated());

        return (new AnswerResource($answer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Answer $answer)
    {
        return new AnswerResource($answer);
    }

    public function update(
        UpdateAnswerRequest $request,
        Answer $answer
    ) {
        $answer->update($request->validated());

        return new AnswerResource($answer);
    }

    public function destroy(Answer $answer)
    {
        $answer->delete();

        return response()->noContent();
    }
}
