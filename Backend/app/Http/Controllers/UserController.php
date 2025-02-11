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
        $email = $request->input('email');
        $password = $request->input('password');
        $positionId = $request->input('positionId');  // Retrieve positionId

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        }

        // Check if the positionId matches the user's positionId
        if ($user->positionId !== $positionId) {
            return response()->json([
                'message' => 'Position mismatch',
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        }

        // Clear previous tokens if needed
        $user->tokens()->delete();

        $token = $user->createToken('access')->plainTextToken;
        $user->token = $token;

        return response()->json([
            'message' => 'ok',
            'data' => $user
        ], JSON_UNESCAPED_UNICODE);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        $personalAccessToken = PersonalAccessToken::findToken($token);

        if ($personalAccessToken) {
            $personalAccessToken->delete();
            return response()->json([
                'message' => 'ok',
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'message' => 'Token not found',
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function index()
    {
        $rows = User::all();
        return response()->json([
            'message' => 'ok',
            'data' => $rows
        ], JSON_UNESCAPED_UNICODE);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->all();

        // Validate positionId
        if (!$request->has('positionId') || !in_array($request->positionId, [1, 2])) {
            return response()->json([
                'message' => 'Invalid positionId. It must be either 1 (administrator) or 2 (guest).',
            ], JSON_UNESCAPED_UNICODE);
        }

        // Check if email already exists
        if (User::where('email', $request['email'])->exists()) {
            return response()->json([
                'message' => 'This email already exists',
                'data' => ['email' => $request['email']]
            ], JSON_UNESCAPED_UNICODE);
        }

        $user = User::create($data);
        return response()->json([
            'message' => 'ok',
            'data' => $user
        ], JSON_UNESCAPED_UNICODE);
    }

    public function show(int $id)
    {
        $row = User::find($id);
        if ($row) {
            return response()->json([
                'message' => 'ok',
                'data' => $row
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'message' => 'Not found',
                'data' => ['id' => $id]
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $row = User::find($id);
        if ($row) {
            if (User::where('email', $request['email'])->exists()) {
                return response()->json([
                    'message' => 'This email already exists',
                    'data' => ['email' => $request['email']]
                ], JSON_UNESCAPED_UNICODE);
            }

            $row->update($request->all());
            return response()->json([
                'message' => 'ok',
                'data' => $row
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'message' => 'Not found',
                'data' => ['id' => $id]
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy(int $id)
    {
        $row = User::find($id);
        if ($row) {
            $row->delete();
            return response()->json([
                'message' => 'ok',
                'data' => ['id' => $id]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            return response()->json([
                'message' => 'Not found',
                'data' => ['id' => $id]
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
