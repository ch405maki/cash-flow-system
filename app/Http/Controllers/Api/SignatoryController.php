<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Signatory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SignatoryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'status' => 'sometimes|string|in:active,inactive'
            ]);

            $signatory = Signatory::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Signatory created successfully',
                'data' => $signatory,
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'default_status' => 'active'
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'meta' => [
                    'valid_status_values' => ['active', 'inactive']
                ]
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}