<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Models\Answer;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource
     */
    public function index()
    {
        return Answer::with(['attempt', 'question', 'option'])
            ->latest()
            ->paginate(15);
    }

    public function store(StoreAnswerRequest $request)
    {
        $answer = Answer::create($request->validated());

        return response()->json($answer, 201);
    }

    public function show(Answer $answer)
    {
        return $answer->load(['attempt', 'question', 'option']);
    }

    public function update(UpdateAnswerRequest $request, Answer $answer)
    {
        $answer->update($request->validated());

        return $answer;
    }

    public function destroy(Answer $answer)
    {
        $answer->delete();

        return response()->noContent();
    }
}
