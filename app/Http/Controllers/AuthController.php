<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try {
            $response = Http::asForm()->post(config('services.passport.login_endpoint'), [
                'grant_type' => 'password',
                'client_id' => config('services.passport.client_id'),
                'client_secret' => config('services.passport.client_secret'),
                'username' => $request->email,
                'password' => $request->password,
                'scope' => '*'
            ]);

            $status = $response->status();

            if ($response->successful()) {
                return response()->json([
                    'message'  => 'Logged in successfully',
                    'data' => $response->json()
                ], 200);
            } elseif ($status === 400 || $status === 401) {
                return response()->json(['message'  => 'Invalid email or password'], 400);
            }

            return response()->json(['message' => 'Authentication server error'], $status);
        } catch (\Throwable $th) {
            logger('LOGIN_FAILED', ['payload' => $request->all(), 'message' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            return response()->json(['message' => 'Something went wrong']);
        }
    }
}
