<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProfilePicture;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProfilePictureController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuration/ProfilePictures', [
            'profilePictures' => ProfilePicture::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $path = $file->store('profile_pictures', 'public');

        $profilePicture = ProfilePicture::create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ]);

        return redirect()->route('profile-pictures.index')->with('success', 'Picture uploaded.');
    }


    public function destroy(ProfilePicture $profilePicture)
    {
        try {
            // Delete file from storage
            Storage::delete($profilePicture->file_path);
            
            // Delete record from database
            $profilePicture->delete();
            
            return response()->json(['message' => 'Profile picture deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
