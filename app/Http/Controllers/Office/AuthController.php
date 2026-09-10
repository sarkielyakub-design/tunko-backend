<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Http\Requests\Office\LoginRequest;
use App\Http\Resources\Office\OfficeStaffResource;
use App\Models\OfficeStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $staff = OfficeStaff::query()->where('email', $request->validated('email'))->with('office')->first();

        if (!$staff || !Hash::check($request->validated('password'), $staff->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials.'], 401);
        }
        if (!$staff->is_active || !$staff->office?->is_active) {
            return response()->json(['success' => false, 'message' => 'Office staff account or office is inactive.'], 403);
        }

        $staff->update(['last_login_at' => now()]);
        $token = $staff->createToken('office-staff-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Office login successful.',
            'token' => $token,
            'data' => new OfficeStaffResource($staff->fresh('office')),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['success' => true, 'message' => 'Logged out successfully.']);
    }

    public function profile(Request $request)
    {
        $staff = $request->user()->load('office');
        return response()->json(['success' => true, 'data' => new OfficeStaffResource($staff)]);
    }
}
