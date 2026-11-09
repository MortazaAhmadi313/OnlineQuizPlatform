<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leaderboard;
use App\Http\Resources\LeaderboardResource;

class LeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Leaderboard::query()->with(['quiz', 'user']);

        // Admin can view all leaderboard entries.
        if ($user->role === 'admin') {
            // No additional restriction.
        } elseif ($user->role === 'teacher') {
            // Teachers can view leaderboards for their own quizzes.
            $query->whereHas('quiz', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        } else {
            // Students can view leaderboards for published quizzes only.
            $query->whereHas('quiz', function ($q) {
                $q->where('status', 'published');
            });
        }

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->query('quiz_id'));
        }

        // Students cannot use this filter to access another student's
        // private results through the leaderboard endpoint.
        if ($user->role !== 'student' && $request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        $sort = $request->query('sort', 'rank');
        $direction = $request->query('direction', 'asc');

        if (! in_array($sort, ['rank', 'score', 'created_at'], true)) {
            $sort = 'rank';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $perPage = min(
            100,
            max(1, (int) $request->query('per_page', 15))
        );

        return LeaderboardResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(Request $request)
    {
        abort(
            403,
            'Leaderboard entries are generated automatically from quiz results.'
        );
    }

    public function show(Request $request, Leaderboard $leaderboard)
    {
        $user = $request->user();

        $allowed = $user->role === 'admin'
            || (
                $user->role === 'teacher'
                && $leaderboard->quiz()
                ->where('created_by', $user->id)
                ->exists()
            )
            || (
                $user->role === 'student'
                && $leaderboard->quiz()
                ->where('status', 'published')
                ->exists()
            );

        abort_unless(
            $allowed,
            403,
            'You are not allowed to view this leaderboard entry.'
        );

        return new LeaderboardResource(
            $leaderboard->load(['quiz', 'user'])
        );
    }

    public function update(Request $request, Leaderboard $leaderboard)
    {
        abort(
            403,
            'Leaderboard entries cannot be edited directly.'
        );
    }

    public function destroy(Leaderboard $leaderboard)
    {
        abort(
            403,
            'Leaderboard entries cannot be deleted directly.'
        );
    }
}
