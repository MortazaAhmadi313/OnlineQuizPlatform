<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttemptRequest;
use App\Http\Requests\UpdateAttemptRequest;
use App\Http\Resources\AttemptResource;
use App\Models\Attempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Question;
use App\Models\Leaderboard;

class AttemptController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Attempt::query();

        if ($user->role === 'teacher') {
            $query->whereHas('quiz', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        } elseif ($user->role === 'student') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->query('quiz_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
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
        $quizId = $request->validated()['quiz_id'];

        $attempt = Attempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quizId,
            'score' => 0,
            'status' => 'in_progress',
            'started_at' => now(),
            'completed_at' => null,
        ]);

        return (new AttemptResource($attempt))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Attempt $attempt)
    {
        $user = $request->user();

        $allowed = $user->role === 'admin'
            || (
                $user->role === 'student'
                && (int) $attempt->user_id === (int) $user->id
            )
            || (
                $user->role === 'teacher'
                && $attempt->quiz()
                ->where('created_by', $user->id)
                ->exists()
            );

        abort_unless(
            $allowed,
            403,
            'You are not allowed to view this attempt.'
        );

        return new AttemptResource($attempt);
    }


    public function submit(Request $request, Attempt $attempt)
    {
        $user = $request->user();

        abort_unless(
            $user->role === 'student'
                && (int) $attempt->user_id === (int) $user->id,
            403,
            'Only the student who owns this attempt can submit it.'
        );

        $attempt = DB::transaction(function () use ($attempt) {
            $attempt = Attempt::whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $attempt->status === 'in_progress',
                422,
                'This attempt has already been submitted or expired.'
            );

            $attempt->load('quiz');

            $deadline = $attempt->started_at->copy()
                ->addMinutes($attempt->quiz->duration);

            $status = now()->greaterThanOrEqualTo($deadline)
                ? 'expired'
                : 'completed';

            // Create records for unanswered questions.
            $questions = Question::where('quiz_id', $attempt->quiz_id)
                ->get();

            foreach ($questions as $question) {
                $unansweredPoints = $question->type === 'essay'
                    ? null
                    : 0;

                $attempt->answers()->firstOrCreate(
                    [
                        'question_id' => $question->id,
                    ],
                    [
                        'option_id' => null,
                        'answer_text' => null,
                        'awarded_points' => $unansweredPoints,
                    ]
                );
            }

            // Calculate the points earned so far.
            $score = $attempt->answers()
                ->whereNotNull('awarded_points')
                ->sum('awarded_points');

            $attempt->update([
                'score' => $score,
                'status' => $status,
                'completed_at' => now(),
            ]);

            return $attempt->fresh(['quiz']);
        });

        if (
            in_array($attempt->status, ['completed', 'expired'], true)
            && ! Question::query()
                ->where('quiz_id', $attempt->quiz_id)
                ->where('type', 'essay')
                ->exists()
        ) {
            Leaderboard::updateBestScore($attempt);
        }
        
        return response()->json([
            'message' => $attempt->status === 'expired'
                ? 'The attempt has expired.'
                : 'The attempt was submitted successfully.',
            'attempt' => new \App\Http\Resources\AttemptResource($attempt),
        ]);
    }



    public function update(
        UpdateAttemptRequest $request,
        Attempt $attempt
    ) {
        $attempt->update($request->validated());

        return new AttemptResource($attempt);
    }

    public function destroy(Request $request, Attempt $attempt)
    {
        abort_unless(
            $request->user()->role === 'admin',
            403,
            'Only administrators can delete attempts.'
        );

        $attempt->delete();

        return response()->noContent();
    }
}
