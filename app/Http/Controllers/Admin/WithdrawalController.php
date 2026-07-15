<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Withdrawal\IndexWithdrawalRequest;
use App\Http\Requests\Admin\Withdrawal\ApproveWithdrawalRequest;
use App\Http\Requests\Admin\Withdrawal\RejectWithdrawalRequest;
use App\Http\Requests\Admin\Withdrawal\CancelWithdrawalRequest;
use App\Http\Requests\Admin\Withdrawal\RetryWithdrawalRequest;
use App\Http\Resources\Admin\WithdrawalCollection;
use App\Http\Resources\Admin\WithdrawalResource;
use App\Models\Withdrawal;
use App\Services\Admin\WithdrawalService;

class WithdrawalController extends AdminController
{
    public function __construct(
        protected WithdrawalService $service
    ) {
    }

    /**
     * Withdrawal List
     */
    public function index(
        IndexWithdrawalRequest $request
    )
    {
        return new WithdrawalCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Withdrawal Details
     */
    public function show(
        Withdrawal $withdrawal
    )
    {
        return $this->success(

            new WithdrawalResource(

                $this->service->show(
                    $withdrawal
                )

            )

        );
    }

    /**
     * Approve Withdrawal
     */
    public function approve(
        ApproveWithdrawalRequest $request,
        Withdrawal $withdrawal
    )
    {
        $withdrawal = $this->service->approve(

            $withdrawal,

            $request->validated()

        );

        return $this->success(

            new WithdrawalResource(
                $withdrawal
            ),

            'Withdrawal approved successfully.'

        );
    }

    /**
     * Reject Withdrawal
     */
    public function reject(
        RejectWithdrawalRequest $request,
        Withdrawal $withdrawal
    )
    {
        $withdrawal = $this->service->reject(

            $withdrawal,

            $request->validated()

        );

        return $this->success(

            new WithdrawalResource(
                $withdrawal
            ),

            'Withdrawal rejected successfully.'

        );
    }

    /**
     * Cancel Withdrawal
     */
    public function cancel(
        CancelWithdrawalRequest $request,
        Withdrawal $withdrawal
    )
    {
        $withdrawal = $this->service->cancel(

            $withdrawal,

            $request->validated()

        );

        return $this->success(

            new WithdrawalResource(
                $withdrawal
            ),

            'Withdrawal cancelled successfully.'

        );
    }

    /**
     * Retry Withdrawal
     */
    public function retry(
        RetryWithdrawalRequest $request,
        Withdrawal $withdrawal
    )
    {
        $withdrawal = $this->service->retry(

            $withdrawal,

            $request->validated()

        );

        return $this->success(

            new WithdrawalResource(
                $withdrawal
            ),

            'Withdrawal queued for retry.'

        );
    }

    /**
     * Statistics
     */
    public function statistics()
    {
        return $this->success(

            $this->service->statistics()

        );
    }
}