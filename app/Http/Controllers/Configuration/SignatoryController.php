<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Signatory;
use App\Services\ActivityLogger;
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

        ActivityLogger::make($request)
            ->log("Signatory \"{$signatory->full_name}\" created");

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

        ActivityLogger::make($request)
            ->log("Signatory \"{$signatory->full_name}\" updated");

        return response()->json($signatory);
    }

    public function destroy(Request $request, Signatory $signatory)
    {
        $name = $signatory->full_name;
        $signatory->delete();

        ActivityLogger::make($request)
            ->log("Signatory \"{$name}\" deleted");

        return response()->json(null, 204);
    }
}