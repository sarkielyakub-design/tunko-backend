<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Kyc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    /**
     * Submit/update the user's KYC personal information.
     */
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
            [
                'user_id' => $userId,
            ],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted successfully.',
            'kyc' => $kyc,
        ]);
    }

    /**
     * Upload KYC identity documents.
     *
     * Flutter sends:
     * front_document
     * back_document
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'front_document' => 'required|file|image|max:5120',
            'back_document' => 'nullable|file|image|max:5120',
        ]);

        $userId = auth()->id();

        $kyc = Kyc::where('user_id', $userId)->first();

        if (!$kyc) {
            return response()->json([
                'success' => false,
                'message' => 'KYC information not found. Please submit your personal information first.',
            ], 404);
        }

        /*
         * Delete previous front document if one exists.
         */
        if (
            $kyc->id_front &&
            Storage::disk('public')->exists(
                $kyc->id_front
            )
        ) {
            Storage::disk('public')->delete(
                $kyc->id_front
            );
        }

        /*
         * Store front document.
         */
        $frontPath = $request
            ->file('front_document')
            ->store(
                'kyc/documents',
                'public'
            );

        $data = [
            'id_front' => $frontPath,
        ];

        /*
         * Store back document if supplied.
         */
        if ($request->hasFile('back_document')) {

            if (
                $kyc->id_back &&
                Storage::disk('public')->exists(
                    $kyc->id_back
                )
            ) {
                Storage::disk('public')->delete(
                    $kyc->id_back
                );
            }

            $backPath = $request
                ->file('back_document')
                ->store(
                    'kyc/documents',
                    'public'
                );

            $data['id_back'] = $backPath;
        }

        $kyc->update($data);

        $kyc = $kyc->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Identity document uploaded successfully.',
            'kyc' => $kyc,
            'documents' => [
                'front' => $kyc->id_front
                    ? Storage::disk('public')
                        ->url($kyc->id_front)
                    : null,

                'back' => $kyc->id_back
                    ? Storage::disk('public')
                        ->url($kyc->id_back)
                    : null,
            ],
        ]);
    }

    /**
     * Upload KYC selfie.
     */
    public function uploadSelfie(Request $request)
    {
        $request->validate([
            'selfie' => 'required|file|image|max:5120',
        ]);

        $userId = auth()->id();

        $kyc = Kyc::where('user_id', $userId)->first();

        if (!$kyc) {
            return response()->json([
                'success' => false,
                'message' => 'KYC information not found. Please submit your personal information first.',
            ], 404);
        }

        /*
         * Delete previous selfie if one exists.
         */
        if (
            $kyc->selfie &&
            Storage::disk('public')->exists(
                $kyc->selfie
            )
        ) {
            Storage::disk('public')->delete(
                $kyc->selfie
            );
        }

        /*
         * Store selfie.
         */
        $selfiePath = $request
            ->file('selfie')
            ->store(
                'kyc/selfies',
                'public'
            );

        $kyc->update([
            'selfie' => $selfiePath,
        ]);

        $kyc = $kyc->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Selfie uploaded successfully.',
            'kyc' => $kyc,
            'selfie' => Storage::disk('public')
                ->url($kyc->selfie),
        ]);
    }

    /**
     * Finalize the user's KYC submission.
     */
    public function finalSubmit()
    {
        $userId = auth()->id();

        $kyc = Kyc::where('user_id', $userId)->first();

        if (!$kyc) {
            return response()->json([
                'success' => false,
                'message' => 'KYC information not found. Please complete your KYC information first.',
            ], 404);
        }

        /*
         * Make sure required personal information
         * has already been submitted.
         */
        if (
            empty($kyc->first_name) ||
            empty($kyc->last_name) ||
            empty($kyc->date_of_birth) ||
            empty($kyc->gender) ||
            empty($kyc->nationality) ||
            empty($kyc->occupation) ||
            empty($kyc->source_of_income) ||
            empty($kyc->id_type) ||
            empty($kyc->id_number)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete all required KYC information before final submission.',
            ], 422);
        }

        /*
         * Prevent an already approved KYC from
         * being submitted again.
         */
        if ($kyc->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Your KYC has already been approved.',
                'kyc' => $kyc,
            ], 422);
        }

        /*
         * Final submission moves the KYC into
         * pending review.
         */
        $kyc->update([
            'status' => 'pending',
            'is_verified' => false,
        ]);

        $kyc = $kyc->fresh();

        return response()->json([
            'success' => true,
            'message' => 'KYC submitted successfully and is now pending review.',
            'kyc' => $kyc,
        ]);
    }

    /**
     * Get the authenticated user's KYC status.
     */
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