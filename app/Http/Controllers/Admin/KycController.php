<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Kyc\ApproveKycRequest;
use App\Http\Requests\Admin\Kyc\IndexKycRequest;
use App\Http\Requests\Admin\Kyc\RejectKycRequest;
use App\Http\Resources\Admin\KycCollection;
use App\Http\Resources\Admin\KycResource;
use App\Models\Kyc;
use App\Services\Admin\KycService;
use Illuminate\Http\JsonResponse;

class KycController extends AdminController
{
    public function __construct(
        protected KycService $service
    ) {
    }

    /**
     * List KYC
     */
    public function index(
        IndexKycRequest $request
    ): KycCollection {
        return new KycCollection(
            $this->service->index(
                $request->validated()
            )
        );
    }

    /**
     * KYC Details
     */
    public function show(
        Kyc $kyc
    ): JsonResponse {
        return $this->success(
            new KycResource(
                $this->service->show($kyc)
            )
        );
    }

    /**
     * Approve KYC
     */
    public function approve(
        ApproveKycRequest $request,
        Kyc $kyc
    ): JsonResponse {
        $kyc = $this->service->approve(
            $kyc,
            $request->validated()
        );

        return $this->success(
            new KycResource($kyc),
            'KYC approved successfully.'
        );
    }

    /**
     * Reject KYC
     */
    public function reject(
        RejectKycRequest $request,
        Kyc $kyc
    ): JsonResponse {
        $kyc = $this->service->reject(
            $kyc,
            $request->validated()
        );

        return $this->success(
            new KycResource($kyc),
            'KYC rejected successfully.'
        );
    }

    /**
     * KYC Statistics
     */
    public function statistics(): JsonResponse
    {
        return $this->success(
            $this->service->statistics()
        );
    }
}