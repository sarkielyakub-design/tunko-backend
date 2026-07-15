<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Beneficiary;
use App\Models\User;
use Illuminate\Http\Request;

class BeneficiaryController extends Controller
{
    /**
     * Verify Recipient
     */
    public function verify(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->phone,
            ]
        ]);
    }

    /**
     * Save Beneficiary
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'nickname' => ['nullable', 'string'],
        ]);

        $recipient = User::where(
            'phone',
            $request->phone
        )->first();

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found.',
            ], 404);
        }

        if ($recipient->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot add yourself as beneficiary.',
            ], 422);
        }

        $beneficiary = Beneficiary::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'beneficiary_user_id' => $recipient->id,
            ],
            [
                'nickname' => $request->nickname,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary saved successfully.',
            'beneficiary' => [
                'id' => $beneficiary->id,
                'full_name' => $recipient->first_name . ' ' . $recipient->last_name,
                'phone' => $recipient->phone,
                'nickname' => $beneficiary->nickname,
            ]
        ]);
    }

    /**
     * List Beneficiaries
     */
    public function index(Request $request)
    {
        $beneficiaries = Beneficiary::with('beneficiary')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'beneficiaries' => $beneficiaries->map(function ($item) {
                return [
                    'id' => $item->id,
                    'full_name' => $item->beneficiary->first_name . ' ' . $item->beneficiary->last_name,
                    'phone' => $item->beneficiary->phone,
                    'nickname' => $item->nickname,
                ];
            }),
        ]);
    }

    /**
     * Delete Beneficiary
     */
    public function destroy(Beneficiary $beneficiary)
    {
        $beneficiary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Beneficiary removed successfully.',
        ]);
    }
}