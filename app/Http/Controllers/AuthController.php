<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|min:6|max:255'
        ]);

        try {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'])
            ]);
            return response()->json(['message' => 'User registered successfully'], 201);
        } catch (\Throwable $th) {
            logger('REGISTRATION_FAILED', ['payload' => $request->all(), 'message' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            return response()->json(['message' => 'Registration failed'], 500);
        }
    }
}
