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
            'first_name' => 'required',
            'last_name' => 'required',
            'date_of_birth' => 'required|date',
            'gender' => 'required',
            'nationality' => 'required',
            'occupation' => 'required',
            'source_of_income' => 'required',
            'id_type' => 'required',
            'id_number' => 'required',
        ]);

        $kyc = Kyc::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middle_name' => $request->middle_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'marital_status' => $request->marital_status,
                'nationality' => $request->nationality,
                'occupation' => $request->occupation,
                'source_of_income' => $request->source_of_income,
                'id_type' => $request->id_type,
                'id_number' => $request->id_number,
                'status' => 'pending',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted successfully.',
            'kyc' => $kyc,
        ]);
    }

    public function status()
    {
        $kyc = Kyc::where(
            'user_id',
            auth()->id()
        )->first();

        return response()->json([
            'success' => true,
            'kyc' => $kyc,
        ]);
    }
}