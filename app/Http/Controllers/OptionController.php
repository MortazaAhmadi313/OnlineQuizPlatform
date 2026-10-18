<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Models\Option;
use App\Http\Resources\OptionResource;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Option::query();

        if ($request->filled('question_id')) {
            $query->where('question_id', $request->question_id);
        }

        if ($request->filled('search')) {
            $query->where(
                'option_text',
                'like',
                '%' . $request->search . '%'
            );
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

        return OptionResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    public function store(StoreOptionRequest $request)
    {
        $option = Option::create($request->validated());

        return (new OptionResource($option))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Option $option)
    {
        return new OptionResource($option);
    }

    public function update(
        UpdateOptionRequest $request,
        Option $option
    ) {
        $option->update($request->validated());

        return new OptionResource($option);
    }

    public function destroy(Option $option)
    {
        $option->delete();

        return response()->noContent();
    }
}
