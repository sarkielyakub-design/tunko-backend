<?php

namespace App\Services\Admin;

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KycService
{
    /**
     * List KYC
     */
    public function index(array $filters): LengthAwarePaginator
    {
        return Kyc::query()

            ->with([
                'user',
                'reviewer',
            ])

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->whereHas('user', function ($user) use ($search) {

                        $user->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");

                    });

                }
            )

            ->when(
                $filters['status'] ?? null,
                fn ($q, $status) => $q->where('status', $status)
            )

            ->when(
                $filters['level'] ?? null,
                fn ($q, $level) => $q->where('level', $level)
            )

            ->when(
                $filters['document_type'] ?? null,
                fn ($q, $type) => $q->where('document_type', $type)
            )

            ->when(
                $filters['country'] ?? null,
                fn ($q, $country) => $q->where('document_country', $country)
            )

            ->latest()

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Show KYC
     */
    public function show(Kyc $kyc): Kyc
    {
        return $kyc->load([
            'user',
            'reviewer',
        ]);
    }

    /**
     * Approve KYC
     */
    public function approve(
        Kyc $kyc,
        array $data
    ): Kyc {

        return DB::transaction(function () use ($kyc, $data) {

            $kyc->update([

                'status' => 'approved',

                'level' => $data['kyc_level'],

                'is_verified' => true,

                'verification_provider' => $data['verification_provider'] ?? null,

                'provider_reference' => $data['provider_reference'] ?? null,

                'reviewed_by' => Auth::guard('admin')->id(),

                'reviewed_at' => now(),

                'approved_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            User::findOrFail($kyc->user_id)
                ->update([
                    'is_verified' => true,
                ]);

            return $kyc->fresh()->load([
                'user',
                'reviewer',
            ]);

        });

    }

    /**
     * Reject KYC
     */
    public function reject(
        Kyc $kyc,
        array $data
    ): Kyc {

        return DB::transaction(function () use ($kyc, $data) {

            $kyc->update([

                'status' => 'rejected',

                'rejection_reason' => $data['reason'],

                'reject_code' => $data['reject_code'],

                'reviewed_by' => Auth::guard('admin')->id(),

                'reviewed_at' => now(),

                'rejected_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            return $kyc->fresh()->load([
                'user',
                'reviewer',
            ]);

        });

    }

    /**
     * Statistics
     */
    public function statistics(): array
    {
        return [

            'total' => Kyc::count(),

            'pending' => Kyc::pending()->count(),

            'approved' => Kyc::approved()->count(),

            'rejected' => Kyc::rejected()->count(),

            'under_review' => Kyc::underReview()->count(),

        ];
    }
}