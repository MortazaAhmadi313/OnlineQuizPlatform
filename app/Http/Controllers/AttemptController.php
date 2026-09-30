<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreAttemptRequest;
use App\Http\Requests\UpdateAttemptRequest;
use App\Models\Attempt;

class AttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Attempt::with(['user', 'quiz'])
            ->latest()
            ->paginate(15);
    }

    public function store(StoreAttemptRequest $request)
    {
        $attempt = Attempt::create($request->validated());

        return response()->json($attempt, 201);
    }

    public function show(Attempt $attempt)
    {
        return $attempt->load(['user', 'quiz', 'answers']);
    }

    public function update(UpdateAttemptRequest $request, Attempt $attempt)
    {
        $attempt->update($request->validated());

        return $attempt;
    }

    public function destroy(Attempt $attempt)
    {
        $attempt->delete();

        return response()->noContent();
    }
}
