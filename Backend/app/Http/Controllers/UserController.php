<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    public function login(LoginUserRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'data' => []
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $user->tokens()->delete();
        $token = $user->createToken('access')->plainTextToken;

        return response()->json([
            'message' => 'ok',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function changePassword(Request $request)
    {
        // Validate the input
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user(); // Get the authenticated user

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        $accessToken = PersonalAccessToken::findToken($token);

        if ($accessToken) {
            $accessToken->delete();
            return response()->json([
                'message' => 'ok',
                'data' => []
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'message' => 'Token not found',
            'data' => []
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    public function index()
    {
        $users = User::orderBy('id', 'asc')->get();
        return response()->json([
            'message' => 'ok',
            'data' => $users
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        return response()->json([
            'message' => 'User created',
            'data' => $user
        ], 201, [], JSON_UNESCAPED_UNICODE);
    }

    public function show(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Not found',
                'data' => ['id' => $id]
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'message' => 'ok',
            'data' => $user
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function update(UpdateUserRequest $request, int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Not found',
                'data' => ['id' => $id]
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $user->update($request->validated());
        return response()->json([
            'message' => 'User updated',
            'data' => $user
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }


    public function destroy(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Not found',
                'data' => ['id' => $id]
            ], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $user->delete();
        return response()->json([
            'message' => 'User deleted',
            'data' => ['id' => $id]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}