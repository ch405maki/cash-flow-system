<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Access;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    public function index()
    {
        return Access::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prognam_name' => 'required|string|max:255',
            'access_level' => 'required|string|max:255'
        ]);

        $access = Access::create($validated);

        return response()->json([
            'message' => 'Access level created successfully',
            'data' => $access
        ], 201);
    }
}