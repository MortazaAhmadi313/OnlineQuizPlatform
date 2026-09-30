<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Question::with(['quiz', 'options'])
            ->latest()
            ->paginate(15);
    }

    public function store(StoreQuestionRequest $request)
    {
        $question = Question::create($request->validated());

        return response()->json($question, 201);
    }

    public function show(Question $question)
    {
        return $question->load(['quiz', 'options']);
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $question->update($request->validated());

        return $question;
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->noContent();
    }
}
