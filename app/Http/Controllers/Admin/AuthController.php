<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends AdminController
{
    /**
     * Admin Login
     */
    public function login(Request $request)
{
    try {

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        \Log::info('Step 1');

        $guard = Auth::guard('admin');

        \Log::info('Step 2');

        if (! $guard->attempt($credentials)) {

            \Log::info('Invalid credentials');

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        \Log::info('Step 3');

        $admin = $guard->user();

        \Log::info('Step 4');

        $token = $admin->createToken('admin-token')->plainTextToken;

        \Log::info('Step 5');

        return response()->json([
            'success' => true,
            'token' => $token,
            'admin' => $admin,
        ]);

    } catch (\Throwable $e) {

        \Log::error($e);

        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }
}
    /**
     * Admin Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Admin Profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}