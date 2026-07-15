<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    /**
     * Resend OTP
     */
    public function send(Request $request)
    {
        $user = $request->user();

        Otp::where('user_id', $user->id)
            ->where('type', 'verification')
            ->where('is_used', false)
            ->delete();

        $code = random_int(100000, 999999);

        Otp::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'code'       => (string) $code,
            'type'       => 'verification',
            'expires_at' => now()->addMinutes(10),
        ]);

        // TODO:
        // Send SMS or Email here.

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
        ]);
    }

    /**
     * Verify OTP
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        $otp = Otp::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('type', 'verification')
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        if ($otp->expires_at->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.',
            ], 422);
        }

        $otp->update([
            'is_used' => true,
        ]);

        $user->update([
            'is_verified' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
        ]);
    }
}