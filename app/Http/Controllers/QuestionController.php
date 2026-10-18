<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Http\Resources\QuestionResource;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return QuestionResource::collection(
            Question::with('options')
                ->latest()
                ->paginate(15)
        );
    }

    public function store(StoreQuestionRequest $request)
    {
        $question = Question::create($request->validated());

        return (new QuestionResource($question))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Question $question)
    {
        return new QuestionResource(
            $question->load('options')
        );
    }

    public function update(
        UpdateQuestionRequest $request,
        Question $question
    ) {
        $question->update($request->validated());

        return new QuestionResource($question);
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->noContent();
    }
}
