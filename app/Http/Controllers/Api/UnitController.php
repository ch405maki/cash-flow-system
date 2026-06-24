<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;

class UnitController extends Controller
{
    public function index(): JsonResponse
    {
        $units = Unit::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $units,
        ]);
    }

    public static function findOrCreate(string $name): Unit
    {
        $normalized = trim($name);

        return Unit::firstOrCreate(
            ['name' => $normalized]
        );
    }
}
