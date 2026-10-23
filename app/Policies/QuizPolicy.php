<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher', 'student'], true);
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'teacher') {
            return $quiz->created_by === $user->id;
        }

        return $user->role === 'student'
            && $quiz->status === 'published';
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'teacher'], true);
    }

    public function update(User $user, Quiz $quiz): bool
    {
        return $user->role === 'admin'
            || (
                $user->role === 'teacher'
                && $quiz->created_by === $user->id
            );
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->update($user, $quiz);
    }

    public function restore(User $user, Quiz $quiz): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Quiz $quiz): bool
    {
        return $user->role === 'admin';
    }
}
