<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use App\Http\Resources\QuizResource;

class QuizController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Quiz::query();

        if ($user->role === 'teacher') {
            $query->where('created_by', $user->id);
        } elseif ($user->role === 'student') {
            $query->where('status', 'published');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status') && $user->role !== 'student') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        if (! in_array($sort, ['title', 'duration', 'created_at'], true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));

        return QuizResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(StoreQuizRequest $request)
    {
        $this -> authorize('create', Quiz::class);

        $data = $request->validated();

        $data['created_by'] = $request->user()->id;

        $quiz = Quiz::create($data);

        return (new QuizResource($quiz))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Quiz $quiz)
    {
        $this -> authorize('view', $quiz);

        return new QuizResource($quiz);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $this -> authorize('update', $quiz);

        $quiz->update($request->validated());

        return new QuizResource($quiz);
    }

    public function destroy(Quiz $quiz)
    {
        $this -> authorize('delete', $quiz);

        $quiz->delete();

        return response()->noContent();
    }
}
