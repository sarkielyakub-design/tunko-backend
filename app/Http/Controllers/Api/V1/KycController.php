<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kyc;
use Illuminate\Http\Request;

class KycController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:50',
            'marital_status' => 'nullable|string|max:50',
            'nationality' => 'required|string|max:100',
            'occupation' => 'required|string|max:150',
            'source_of_income' => 'required|string|max:150',

            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',

            'id_type' => 'required|string|max:100',
            'id_number' => 'required|string|max:100',
            'document_type' => 'nullable|string|max:100',
            'document_country' => 'nullable|string|max:100',

            'id_front' => 'nullable|file|image|max:5120',
            'id_back' => 'nullable|file|image|max:5120',
            'selfie' => 'nullable|file|image|max:5120',
        ]);

        $userId = auth()->id();

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status,
            'nationality' => $request->nationality,
            'occupation' => $request->occupation,
            'source_of_income' => $request->source_of_income,

            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'address' => $request->address,

            'id_type' => $request->id_type,
            'id_number' => $request->id_number,
            'document_type' => $request->document_type,
            'document_country' => $request->document_country,

            'status' => 'pending',
            'is_verified' => false,
        ];

        $kyc = Kyc::updateOrCreate(
            ['user_id' => $userId],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted successfully.',
            'kyc' => $kyc,
        ]);
    }

    public function status()
    {
        $kyc = Kyc::where('user_id', auth()->id())->first();

        return response()->json([
            'success' => true,
            'kyc' => $kyc,
        ]);
    }
}