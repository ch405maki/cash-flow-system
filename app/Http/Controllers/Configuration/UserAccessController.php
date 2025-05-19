<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Access;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserAccessController extends Controller
{
    public function index()
    {
        $accesses = Access::all();
        return Inertia::render('Configuration/Access', [
            'accesses' => $accesses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_name' => 'required|string|max:255',
            'access_level' => 'required|string|max:255',
        ]);

        $access = Access::create($validated);

        return response()->json($access, 201);
    }

    public function update(Request $request, Access $access)
    {
        $validated = $request->validate([
            'program_name' => 'sometimes|string|max:255',
            'access_level' => 'sometimes|string|max:255',
        ]);

        $access->update($validated);

        return response()->json($access);
    }

    public function destroy(Access $access)
    {
        $access->delete();
        return response()->json(null, 204);
    }
}