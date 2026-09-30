<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaderboardRequest;
use App\Http\Requests\UpdateLeaderboardRequest;
use App\Models\Leaderboard;

class LeaderboardController extends Controller
{
    public function index()
    {
        return Leaderboard::with(['quiz', 'user', 'attempt'])
            ->latest()
            ->paginate(15);
    }

    public function store(StoreLeaderboardRequest $request)
    {
        $leaderboard = Leaderboard::create($request->validated());

        return response()->json($leaderboard, 201);
    }

    public function show(Leaderboard $leaderboard)
    {
        return $leaderboard->load(['quiz', 'user', 'attempt']);
    }

    public function update(UpdateLeaderboardRequest $request, Leaderboard $leaderboard)
    {
        $leaderboard->update($request->validated());

        return $leaderboard;
    }

    public function destroy(Leaderboard $leaderboard)
    {
        $leaderboard->delete();

        return response()->noContent();
    }
}