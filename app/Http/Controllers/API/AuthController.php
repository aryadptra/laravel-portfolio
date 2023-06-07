<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        // If validation fails, return error message
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Create user
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return token
        return response()->json([
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        // If validation fails, return error message
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        // If user doesn't exist, return error message
        if (!$user) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        // Check if password is correct
        if (!Auth::attempt($validator->validated())) {
            return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return token
        return response()->json([
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        // Revoke token
        $request->user()->currentAccessToken()->delete();

        // Return success message
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
