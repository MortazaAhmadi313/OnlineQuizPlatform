<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $user = $request->user();

        // Store the new avatar
        $newPath = $validated['avatar']->store('avatars', 'public');

        // Delete the old avatar after successful upload
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Update the user's avatar path
        $user->avatar_path = $newPath;
        $user->save();

        return response()->json([
            'message' => 'Profile picture uploaded successfully.',
            'user' => new UserResource($user),
        ]);
    }
}