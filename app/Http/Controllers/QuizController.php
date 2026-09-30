<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Quiz::with(['category', 'creator'])
            ->latest()
            ->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuizRequest $request)
    {
        $quiz = Quiz::create($request->validated());

        return response()->json($quiz, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        return $quiz->load(['category', 'creator', 'questions']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $quiz->update($request->validated());

        return $quiz;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return response()->noContent();
    }
}
