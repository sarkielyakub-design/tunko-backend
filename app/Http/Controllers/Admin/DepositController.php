<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Deposit\IndexDepositRequest;
use App\Http\Requests\Admin\Deposit\ApproveDepositRequest;
use App\Http\Requests\Admin\Deposit\RejectDepositRequest;
use App\Http\Requests\Admin\Deposit\CancelDepositRequest;
use App\Http\Resources\Admin\DepositCollection;
use App\Http\Resources\Admin\DepositResource;
use App\Models\Deposit;
use App\Services\Admin\DepositService;

class DepositController extends AdminController
{
    public function __construct(
        protected DepositService $service
    ) {
    }

    /**
     * Deposit List
     */
    public function index(
        IndexDepositRequest $request
    )
    {
        return new DepositCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Deposit Details
     */
    public function show(
        Deposit $deposit
    )
    {
        return $this->success(

            new DepositResource(

                $this->service->show(
                    $deposit
                )

            )

        );
    }

    /**
     * Approve Deposit
     */
    public function approve(
        ApproveDepositRequest $request,
        Deposit $deposit
    )
    {
        $deposit = $this->service->approve(

            $deposit,

            $request->validated()

        );

        return $this->success(

            new DepositResource(
                $deposit
            ),

            'Deposit approved successfully.'

        );
    }

    /**
     * Reject Deposit
     */
    public function reject(
        RejectDepositRequest $request,
        Deposit $deposit
    )
    {
        $deposit = $this->service->reject(

            $deposit,

            $request->validated()

        );

        return $this->success(

            new DepositResource(
                $deposit
            ),

            'Deposit rejected successfully.'

        );
    }

    /**
     * Cancel Deposit
     */
    public function cancel(
        CancelDepositRequest $request,
        Deposit $deposit
    )
    {
        $deposit = $this->service->cancel(

            $deposit,

            $request->validated()

        );

        return $this->success(

            new DepositResource(
                $deposit
            ),

            'Deposit cancelled successfully.'

        );
    }

    /**
     * Deposit Statistics
     */
    public function statistics()
    {
        return $this->success(

            $this->service->statistics()

        );
    }
}