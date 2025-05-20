<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Signatory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SignatoryController extends Controller
{
    public function index()
    {
        $signatories = Signatory::all();
        return Inertia::render('Configuration/Signatory', [
            'signatories' => $signatories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $signatory = Signatory::create($validated);

        return response()->json($signatory, 201);
    }

    public function update(Request $request, Signatory $signatory)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $signatory->update($validated);
        
        return response()->json($signatory);
    }

    public function destroy(Signatory $signatory)
    {
        $signatory->delete();
        return response()->json(null, 204);
    }
}