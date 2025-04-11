<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    public function login(LoginUserRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user->tokens()->delete();

        // Issue token to ALL users (including guests)
        $token = $user->createToken('basic-access', ['change-password'])->plainTextToken;

        return response()->json([
            'message' => 'ok',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|max:16|confirmed',
            'email' => 'required_if:token,false|email' // Require email if no token
        ]);

        // Authenticate via token or email/password
        if ($request->bearerToken()) {
            $user = $request->user(); // Token-based auth
        } else {
            // Email + password auth for guests
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function updateName(Request $request, int $id)
    {
        // You can also check that the authenticated user's id matches $id if needed
        if (!$request->user() || $request->user()->id !== $id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        $request->validate([
            'name' => 'required|string|min:2|max:255|unique:users,name,' . $id,
        ]);
    
        $updated = DB::update('UPDATE users SET name = ? WHERE id = ?', [
            $request->input('name'),
            $id,
        ]);
    
        if ($updated) {
            $user = DB::table('users')->select('id', 'name', 'email')
                ->where('id', $id)
                ->first();
            return response()->json([
                'message' => 'Name updated successfully!',
                'user' => $user,
            ]);
        } else {
            return response()->json(['message' => 'Failed to update name.'], 500);
        }
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

        // Generate token immediately after registration
        $token = $user->createToken('registration-token')->plainTextToken;

        return response()->json([
            'message' => 'User created',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
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
        $row = User::find($id);
        if ($row) {
            $row->delete();
            $data = [
                'message' => 'ok',
                'data' => [
                    'id' => $id
                ]
            ];
        } else {
            $data = [
                'message' => 'Not found',
                'data' => [
                    'id' => $id
                ]
            ];
        }
        return response()->json($data, options: JSON_UNESCAPED_UNICODE);
    }
}
