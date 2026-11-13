<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\Attempt;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'admin' => $this->adminDashboard(),
            'teacher' => $this->teacherDashboard($user),
            'student' => $this->studentDashboard($user),
            default => response()->json([
                'message' => 'Unauthorized role.',
            ], 403),
        };
    }

    /**
     * Admin Dashboard
     */
    private function adminDashboard()
    {
        $recentAttempts = Attempt::with([
            'user:id,name',
            'quiz:id,title',
        ])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($attempt) => [
                'id' => $attempt->id,
                'student' => $attempt->user?->name,
                'quiz' => $attempt->quiz?->title,
                'score' => $attempt->score,
                'status' => $attempt->status,
                'completed_at' => $attempt->completed_at,
            ]);

        return response()->json([
            'role' => 'admin',

            'stats' => [
                'total_users' => User::count(),
                'total_students' => User::where('role', 'student')->count(),
                'total_teachers' => User::where('role', 'teacher')->count(),
                'total_quizzes' => Quiz::count(),
                'published_quizzes' => Quiz::where('status', 'published')->count(),
                'total_categories' => Category::count(),
                'total_attempts' => Attempt::count(),
            ],

            'recent_activity' => $recentAttempts,
        ]);
    }

    /**
     * Teacher Dashboard
     */
    private function teacherDashboard(User $user)
    {
        $quizIds = Quiz::where('created_by', $user->id)
            ->select('id');

        $recentAttempts = Attempt::with([
            'user:id,name',
            'quiz:id,title',
        ])
            ->whereHas('quiz', function ($query) use ($user) {
                $query->where('created_by', $user->id);
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($attempt) => [
                'id' => $attempt->id,
                'student' => $attempt->user?->name,
                'quiz' => $attempt->quiz?->title,
                'score' => $attempt->score,
                'status' => $attempt->status,
                'completed_at' => $attempt->completed_at,
            ]);

        return response()->json([
            'role' => 'teacher',

            'stats' => [
                'total_quizzes' => Quiz::where('created_by', $user->id)->count(),

                'published_quizzes' => Quiz::where('created_by', $user->id)
                    ->where('status', 'published')
                    ->count(),

                'total_attempts' => Attempt::whereIn('quiz_id', $quizIds)->count(),

                'total_students' => Attempt::whereIn('quiz_id', $quizIds)
                    ->distinct('user_id')
                    ->count('user_id'),
            ],

            'recent_activity' => $recentAttempts,
        ]);
    }

    /**
     * Student Dashboard
     */
    private function studentDashboard(User $user)
    {
        $recentAttempts = Attempt::with([
            'quiz.questions',
            'answers',
        ])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($attempt) {
                $essayQuestions = $attempt->quiz?->questions
                    ->where('type', 'essay') ?? collect();

                $finalScoreReady = $attempt->status !== 'in_progress'
                    && $essayQuestions->every(function ($question) use ($attempt) {
                        return $attempt->answers->contains(function ($answer) use ($question) {
                            return $answer->question_id === $question->id
                                && $answer->awarded_points !== null;
                        });
                    });

                return [
                    'id' => $attempt->id,
                    'quiz' => $attempt->quiz?->title,
                    'score' => $finalScoreReady ? $attempt->score : null,
                    'final_score_ready' => $finalScoreReady,
                    'status' => $attempt->status,
                    'started_at' => $attempt->started_at,
                    'completed_at' => $attempt->completed_at,
                ];
            });

        $completedAttempts = Attempt::with([
            'quiz.questions',
            'answers',
        ])
            ->where('user_id', $user->id)
            ->whereIn('status', ['completed', 'expired'])
            ->get();

        $bestScore = $completedAttempts
            ->filter(function ($attempt) {
                $essayQuestions = $attempt->quiz?->questions
                    ->where('type', 'essay') ?? collect();

                return $essayQuestions->every(function ($question) use ($attempt) {
                    return $attempt->answers->contains(function ($answer) use ($question) {
                        return $answer->question_id === $question->id
                            && $answer->awarded_points !== null;
                    });
                });
            })
            ->max('score') ?? 0;

        return response()->json([
            'role' => 'student',
            'stats' => [
                'available_quizzes' => Quiz::where('status', 'published')->count(),
                'total_attempts' => Attempt::where('user_id', $user->id)->count(),
                'completed_attempts' => Attempt::where('user_id', $user->id)
                    ->whereIn('status', ['completed', 'expired'])
                    ->count(),
                'in_progress_attempts' => Attempt::where('user_id', $user->id)
                    ->where('status', 'in_progress')
                    ->count(),
                'best_score' => $bestScore,
            ],
            'recent_activity' => $recentAttempts,
        ]);
    }
}
