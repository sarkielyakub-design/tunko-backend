<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    /**
     * Create Transaction PIN
     */
    public function create(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'digits:4', 'confirmed'],
        ]);

        $user = $request->user();

        if ($user->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction PIN already exists.'
            ], 422);
        }

        $user->update([
            'transaction_pin' => Hash::make($request->pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN created successfully.'
        ]);
    }

    /**
     * Verify Transaction PIN
     */
    public function verify(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'digits:4'],
        ]);

        $user = $request->user();

        if (!$user->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction PIN not found.'
            ], 404);
        }

        if (!Hash::check($request->pin, $user->transaction_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN verified.'
        ]);
    }

    /**
     * Change Transaction PIN
     */
    public function change(Request $request)
    {
        $request->validate([
            'current_pin' => ['required', 'digits:4'],
            'new_pin' => ['required', 'digits:4', 'confirmed'],
        ]);

        $user = $request->user();

        if (!$user->transaction_pin) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction PIN not found.'
            ], 404);
        }

        if (!Hash::check($request->current_pin, $user->transaction_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Current PIN is incorrect.'
            ], 422);
        }

        $user->update([
            'transaction_pin' => Hash::make($request->new_pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN changed successfully.'
        ]);
    }

    /**
     * Reset Transaction PIN
     */
    public function reset(Request $request)
    {
        $request->validate([
            'phone' => ['required'],
            'otp' => ['required'],
            'new_pin' => ['required', 'digits:4', 'confirmed'],
        ]);

        $otp = Otp::where('phone', $request->phone)
            ->where('code', $request->otp)
            ->where('is_used', true)
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP verification required.'
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        $user->update([
            'transaction_pin' => Hash::make($request->new_pin),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction PIN reset successfully.'
        ]);
    }
}