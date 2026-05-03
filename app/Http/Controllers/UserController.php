<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users (admin).
     */
    public function index(): JsonResponse
    {
        $users = User::paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users,
        ], 200);
    }

    /**
     * Store a new user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Hash password
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 200);
    }

    /**
     * Update a user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $validated = $request->validated();

            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user.
     * Critical Business Logic: Prevent deletion of last admin user and prevent self-deletion.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Prevent self-deletion
            if (auth()->id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.',
                    'errors' => [
                        'user' => 'Self-deletion is not allowed.',
                    ]
                ], 422);
            }

            // Check if only one user exists in the system
            $totalUsers = User::count();

            if ($totalUsers === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete the last administrator in the system.',
                    'errors' => [
                        'user' => 'At least one administrator must exist in the system.',
                    ]
                ], 422);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
