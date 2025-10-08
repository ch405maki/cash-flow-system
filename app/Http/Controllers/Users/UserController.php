<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use App\Notifications\WelcomeEmail;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use App\Models\Department;
use App\Models\Access;
use App\Models\ProfilePicture;
use Exception;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('profilePicture', 'department')->get();
        $profilePictures = ProfilePicture::all();
        $departments = Department::all();
        $accessLevels = Access::all();

        return Inertia::render('Configuration/Users', [
            'users' => $users,
            'departments' => $departments,
            'accessLevels' => $accessLevels,
            'profilePictures' => $profilePictures,
        ]);
    }
    
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:255|unique:users',
                'first_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|string',
                'status' => 'required|in:active,inactive',
                'department_id' => 'required|exists:departments,id',
                'access_id' => 'required|exists:accesses,id',
                'profile_picture_id' => 'nullable|integer|exists:profile_pictures,id',
                'is_petty_cash' => 'boolean',
                'is_cash_advance' => 'boolean',
            ]);

            // Ensure boolean casting
            $validatedData['is_petty_cash'] = (bool) $request->input('is_petty_cash', false);
            $validatedData['is_cash_advance'] = (bool) $request->input('is_cash_advance', false);


            // Store plain password before hashing
            $plainPassword = $validatedData['password'];

            // Create user
            $user = User::create([
                ...$validatedData,
                'password' => Hash::make($plainPassword),
            ]);

            // Send welcome email with credentials
            $user->notify(new WelcomeEmail($user, $plainPassword));

            // Load relationships for response
            $user->load(['department', 'access', 'profilePicture']);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'resource' => 'users'
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'meta' => [
                    'failed_validation' => true,
                    'validation_rules' => [
                        'username' => 'required|string|max:255|unique:users',
                        'email' => 'required|email|unique:users',
                        // ... other rules
                    ]
                ]
            ], 422);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Please contact support',
                'meta' => [
                    'database_error' => true,
                    'code' => $e->getCode()
                ]
            ], 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'meta' => [
                    'timestamp' => now()->toDateTimeString(),
                    'error_code' => $e->getCode()
                ]
            ], 500);
        }
    }

    public function uploadUsers(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            Excel::import(new UsersImport, $request->file('file'));

            return response()->json(['message' => 'Users uploaded successfully!'], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (e.g., duplicate entry)
            \Log::error('Database error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Duplicate entry found! Some records already exist.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle validation errors inside Excel import
            $failures = $e->failures();
            return response()->json([
                'message' => 'Some rows failed validation.',
                'errors' => $failures
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error uploading users: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to upload file. Please check the format and data integrity.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'username'      => 'required|string|max:255|unique:users,username,' . $id,
                'first_name'    => 'required|string|max:255',
                'middle_name'   => 'nullable|string|max:255',
                'last_name'     => 'required|string|max:255',
                'email'         => 'required|email|max:255|unique:users,email,' . $id,
                'role'          => 'required|in:admin,executive_director,department_head,accounting,audit,property_custodian,purchasing,staff',
                'status'        => 'required|in:active,inactive',
            ]);

            // ✅ Add boolean values manually (convert to true/false)
            $validated['is_petty_cash'] = (bool) $request->input('is_petty_cash', false);
            $validated['is_cash_advance'] = (bool) $request->input('is_cash_advance', false);

            $user->update($validated);

            return response()->json([
                'message' => 'User updated successfully!',
                'user' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateStatus(Request $request, User $user)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'status' => 'required|string|in:active,inactive',
            ]);

            // Log the request data
            \Log::info('Updating user status:', [
                'user_id' => $user->id,
                'new_status' => $validated['status'],
            ]);

            // Update user status
            $user->update($validated);

            return response()->json(['message' => 'User status updated successfully!', 'user' => $user], 200);
        } catch (ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to update user status:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update user status', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
