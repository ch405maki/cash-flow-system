<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
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

        ActivityLogger::make($request)
            ->on($profilePicture)
            ->log("Profile picture \"{$profilePicture->file_name}\" uploaded");

        return redirect()->route('profile-pictures.index')->with('success', 'Picture uploaded.');
    }



}
