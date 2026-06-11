<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use App\Models\ProfilePicture;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfilePictureController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $path = $file->store('profile_pictures', 'public');

        $profilePicture = ProfilePicture::create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ]);

        ActivityLogger::make($request)
            ->on($profilePicture)
            ->log("Profile picture \"{$profilePicture->file_name}\" uploaded");

        return response()->json(['message' => 'Picture uploaded.', 'data' => $profilePicture], 201);
    }

    public function destroy(Request $request, ProfilePicture $profilePicture)
    {
        try {
            $name = $profilePicture->file_name;
            Storage::delete($profilePicture->file_path);

            $profilePicture->delete();

            ActivityLogger::make($request)
                ->log("Profile picture \"{$name}\" deleted");

            return response()->json(['message' => 'Profile picture deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
