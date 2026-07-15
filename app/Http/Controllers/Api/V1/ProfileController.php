<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            "success" => true,
            "data" => [
                "id" => $user->id,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "full_name" => trim($user->first_name . " " . $user->last_name),
                "email" => $user->email,
                "phone" => $user->phone,
                "country" => $user->country,
                "country_code" => $user->country_code,
                "currency" => $user->currency,
                "avatar" => $user->avatar ?? "",
                "is_kyc_verified" => (bool) $user->is_kyc_verified,
                "has_transaction_pin" => !is_null($user->transaction_pin),
            ],
        ]);
    }

    /**
     * Update authenticated user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            "first_name" => "required|string|max:100",
            "last_name"  => "required|string|max:100",
            "email"      => "required|email|max:255",
            "phone"      => "required|string|max:30",
        ]);

        $user = $request->user();

        $user->update([
            "first_name" => $request->first_name,
            "last_name"  => $request->last_name,
            "email"      => $request->email,
            "phone"      => $request->phone,
        ]);

        $user = $user->fresh();

        return response()->json([
            "success" => true,
            "message" => "Profile updated successfully.",
            "data" => [
                "id" => $user->id,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "full_name" => trim($user->first_name . " " . $user->last_name),
                "email" => $user->email,
                "phone" => $user->phone,
                "country" => $user->country,
                "country_code" => $user->country_code,
                "currency" => $user->currency,
                "avatar" => $user->avatar ?? "",
                "is_kyc_verified" => (bool) $user->is_kyc_verified,
                "has_transaction_pin" => !is_null($user->transaction_pin),
            ],
        ]);
    }
}