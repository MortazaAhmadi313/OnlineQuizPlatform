<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreAttemptRequest;
use App\Http\Requests\UpdateAttemptRequest;
use App\Models\Attempt;
use App\Http\Resources\AttemptResource;

class AttemptController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attempt::query();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        if (! in_array($sort, ['score', 'started_at', 'created_at'], true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));

        return AttemptResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(StoreAttemptRequest $request)
    {
        $attempt = Attempt::create($request->validated());

        return (new AttemptResource($attempt))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Attempt $attempt)
    {
        return new AttemptResource($attempt);
    }

    public function update(
        UpdateAttemptRequest $request,
        Attempt $attempt
    ) {
        $attempt->update($request->validated());

        return new AttemptResource($attempt);
    }

    public function destroy(Attempt $attempt)
    {
        $attempt->delete();

        return response()->noContent();
    }
}
