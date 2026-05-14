<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApiLoginRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(ApiLoginRequest $request): JsonResponse
    {
        $user = $request->authenticate();

        $tokenName = $request->input('token_name', 'api-token');

        $token = $user->createToken($tokenName);

        ActivityLogger::make($request)
            ->on($user)
            ->log("User \"{$user->username}\" logged in (API token: {$tokenName})");

        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully',
            'data' => [
                'user' => $user->load('profilePicture'),
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        ActivityLogger::make($request)
            ->on($user)
            ->log("User \"{$user->username}\" logged out (API token revoked)");

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        ActivityLogger::make($request)
            ->on($user)
            ->log("User \"{$user->username}\" revoked all API tokens");

        return response()->json([
            'success' => true,
            'message' => 'All tokens revoked successfully',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load('profilePicture'),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        ActivityLogger::make($request)
            ->on($user)
            ->log("Password changed for user \"{$user->username}\" (API)");

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    public function tokens(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->tokens()->latest()->get(),
        ]);
    }

    public function revokeToken(Request $request, $tokenId): JsonResponse
    {
        $token = PersonalAccessToken::findOrFail($tokenId);

        if ($token->tokenable_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $token->delete();

        ActivityLogger::make($request)
            ->on($request->user())
            ->log("API token #{$token->id} revoked for user \"{$request->user()->username}\"");

        return response()->json([
            'success' => true,
            'message' => 'Token revoked successfully',
        ]);
    }
}
