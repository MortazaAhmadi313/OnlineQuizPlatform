<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeAnswerRequest;
use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Leaderboard;

class AnswerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Answer::query()->with([
            'attempt',
            'question.quiz',
        ]);

        if ($user->role === 'student') {
            $query->whereHas('attempt', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif ($user->role === 'teacher') {
            $query->whereHas('question.quiz', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        }

        if ($request->filled('attempt_id')) {
            $query->where('attempt_id', $request->query('attempt_id'));
        }

        if ($request->filled('question_id')) {
            $query->where('question_id', $request->query('question_id'));
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        if (! in_array($sort, ['created_at'], true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));

        return AnswerResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(StoreAnswerRequest $request)
    {
        $data = $request->validated();

        $answer = DB::transaction(function () use ($data) {
            $question = Question::with('options')->findOrFail(
                $data['question_id']
            );

            $awardedPoints = null;
            $optionId = null;
            $answerText = null;

            if (in_array(
                $question->type,
                ['multiple_choice', 'true_false'],
                true
            )) {
                $optionId = (int) $data['option_id'];

                $option = $question->options->firstWhere('id', $optionId);

                if (! $option) {
                    abort(422, 'The selected option does not belong to this question.');
                }

                $awardedPoints = $option->is_correct
                    ? $question->points
                    : 0;
            } elseif ($question->type === 'short_answer') {
                $answerText = trim($data['answer_text']);
                $submitted = mb_strtolower($answerText);

                $isCorrect = $question->options
                    ->where('is_correct', true)
                    ->contains(function ($option) use ($submitted) {
                        return mb_strtolower(
                            trim($option->option_text)
                        ) === $submitted;
                    });

                $awardedPoints = $isCorrect
                    ? $question->points
                    : 0;
            } elseif ($question->type === 'essay') {
                $answerText = trim($data['answer_text']);
            }

            return Answer::create([
                'attempt_id' => $data['attempt_id'],
                'question_id' => $data['question_id'],
                'option_id' => $optionId,
                'answer_text' => $answerText,
                'awarded_points' => $awardedPoints,
            ]);
        });

        return (new AnswerResource($answer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Answer $answer, Request $request)
    {
        $this->authorizeAnswerAccess($answer, $request->user());

        return new AnswerResource($answer);
    }

    public function update(
        UpdateAnswerRequest $request,
        Answer $answer
    ) {
        $data = $request->validated();

        DB::transaction(function () use ($data, $answer) {
            $question = Question::with('options')->findOrFail(
                $answer->question_id
            );

            if (in_array(
                $question->type,
                ['multiple_choice', 'true_false'],
                true
            )) {
                $optionId = isset($data['option_id'])
                    ? (int) $data['option_id']
                    : (int) $answer->option_id;

                $option = $question->options->firstWhere('id', $optionId);

                if (! $option) {
                    abort(422, 'The selected option does not belong to this question.');
                }

                $answer->option_id = $optionId;
                $answer->awarded_points = $option->is_correct
                    ? $question->points
                    : 0;
            } elseif (in_array(
                $question->type,
                ['short_answer', 'essay'],
                true
            )) {
                if (array_key_exists('answer_text', $data)) {
                    $answer->answer_text = trim($data['answer_text']);
                }

                if ($question->type === 'short_answer') {
                    $submitted = mb_strtolower(
                        trim($answer->answer_text ?? '')
                    );

                    $isCorrect = $question->options
                        ->where('is_correct', true)
                        ->contains(function ($option) use ($submitted) {
                            return mb_strtolower(
                                trim($option->option_text)
                            ) === $submitted;
                        });

                    $answer->awarded_points = $isCorrect
                        ? $question->points
                        : 0;
                } else {
                    $answer->awarded_points = null;
                }
            }

            $answer->save();
        });

        return new AnswerResource($answer->fresh());
    }

    public function grade(
        GradeAnswerRequest $request,
        Answer $answer
    ) {
        $points = $request->validated()['awarded_points'];

        $result = DB::transaction(function () use ($answer, $points) {
            $attempt = $answer->attempt()
                ->lockForUpdate()
                ->firstOrFail();

            $answer->awarded_points = $points;
            $answer->save();

            // Check every essay question in this quiz,
            // including questions the student did not answer.
            $hasUngradedEssays = Question::query()
                ->where('quiz_id', $attempt->quiz_id)
                ->where('type', 'essay')
                ->whereDoesntHave('answers', function ($query) use ($attempt) {
                    $query->where('attempt_id', $attempt->id)
                        ->whereNotNull('awarded_points');
                })
                ->exists();

            $score = $attempt->answers()
                ->whereNotNull('awarded_points')
                ->sum('awarded_points');

            $attempt->score = $score;
            $attempt->save();

            $finalScoreReady = ! $hasUngradedEssays;

            $attempt = $attempt->fresh();

            if ($finalScoreReady) {
                Leaderboard::updateBestScore($attempt);
            }

            return [
                'attempt' => $attempt,
                'final_score_ready' => $finalScoreReady,
            ];
        });

        return response()->json([
            'message' => $result['final_score_ready']
                ? 'All essay questions have been graded. The final score is ready.'
                : 'The answer was graded. Some essay questions still need grading.',
            'final_score_ready' => $result['final_score_ready'],
            'attempt_id' => $result['attempt']->id,
            'score' => $result['final_score_ready']
                ? $result['attempt']->score
                : null,
        ]);
    }


    public function destroy(Answer $answer, Request $request)
    {
        abort_unless(
            $request->user()->role === 'admin',
            403,
            'Only administrators can delete answers.'
        );

        $answer->delete();

        return response()->noContent();
    }

    private function authorizeAnswerAccess(
        Answer $answer,
        $user
    ): void {
        if ($user->role === 'admin') {
            return;
        }

        if (
            $user->role === 'student'
            && $answer->attempt
            && (int) $answer->attempt->user_id === (int) $user->id
        ) {
            return;
        }

        if (
            $user->role === 'teacher'
            && $answer->question
            && $answer->question->quiz
            && (int) $answer->question->quiz->created_by === (int) $user->id
        ) {
            return;
        }

        abort(403, 'You are not allowed to access this answer.');
    }
}
