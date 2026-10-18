<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaderboardRequest;
use App\Http\Requests\UpdateLeaderboardRequest;
use App\Models\Leaderboard;
use App\Http\Resources\LeaderboardResource;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Leaderboard::query();

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $sort = $request->query('sort', 'rank');
        $direction = $request->query('direction', 'asc');

        if (! in_array($sort, ['rank', 'score', 'created_at'], true)) {
            $sort = 'rank';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));

        return LeaderboardResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(StoreLeaderboardRequest $request)
    {
        $leaderboard = Leaderboard::create($request->validated());

        return (new LeaderboardResource($leaderboard))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Leaderboard $leaderboard)
    {
        return new LeaderboardResource($leaderboard);
    }

    public function update(
        UpdateLeaderboardRequest $request,
        Leaderboard $leaderboard
    ) {
        $leaderboard->update($request->validated());

        return new LeaderboardResource($leaderboard);
    }

    public function destroy(Leaderboard $leaderboard)
    {
        $leaderboard->delete();

        return response()->noContent();
    }
}
