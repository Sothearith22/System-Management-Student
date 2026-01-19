<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:4',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'user' => $user,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // login failed
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // authenticated user
            $user = Auth::user();

            // create sanctum token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successfully',
                'token' => $token,
                'data' => [
                    'user' => $user,

                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage(),
            ], 500);
        }

    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}
