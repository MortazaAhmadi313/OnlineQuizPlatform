<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        if (! in_array($sort, ['name', 'created_at'], true)) {
            $sort = 'created_at';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $perPage = min(100, max(1, (int) $request->query('per_page', 15)));

        return UserResource::collection(
            $query->orderBy($sort, $direction)->paginate($perPage)
        );
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        return new UserResource($user);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($user, $data) {
            $lockedUser = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Protect the last administrator from role removal.
            if (
                $lockedUser->role === 'admin'
                && isset($data['role'])
                && $data['role'] !== 'admin'
            ) {
                $adminCount = User::where('role', 'admin')
                    ->lockForUpdate()
                    ->count();

                if ($adminCount <= 1) {
                    return 'last_admin';
                }
            }

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $lockedUser->update($data);

            return 'updated';
        });

        if ($result === 'last_admin') {
            return response()->json([
                'message' => 'The last administrator cannot be demoted.',
            ], 409);
        }

        return new UserResource($user->fresh());
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $result = DB::transaction(function () use ($user) {
            $lockedUser = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Preserve users who have quiz attempt history.
            if ($lockedUser->attempts()->exists()) {
                return 'has_attempts';
            }

            // Protect the last administrator.
            if ($lockedUser->role === 'admin') {
                $adminCount = User::where('role', 'admin')
                    ->lockForUpdate()
                    ->count();

                if ($adminCount <= 1) {
                    return 'last_admin';
                }
            }

            $lockedUser->delete();

            return 'deleted';
        });

        if ($result === 'has_attempts') {
            return response()->json([
                'message' => 'This user cannot be deleted because they have quiz attempt history.',
            ], 409);
        }

        if ($result === 'last_admin') {
            return response()->json([
                'message' => 'The last administrator cannot be deleted.',
            ], 409);
        }

        return response()->noContent();
    }
}