<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Models\Option;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Option::with('question')
            ->latest()
            ->paginate(15);
    }

    public function store(StoreOptionRequest $request)
    {
        $option = Option::create($request->validated());

        return response()->json($option, 201);
    }

    public function show(Option $option)
    {
        return $option->load('question');
    }

    public function update(UpdateOptionRequest $request, Option $option)
    {
        $option->update($request->validated());

        return $option;
    }

    public function destroy(Option $option)
    {
        $option->delete();

        return response()->noContent();
    }
}
