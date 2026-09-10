<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Transfer\IndexTransferRequest;
use App\Http\Requests\Admin\Transfer\ApproveTransferRequest;
use App\Http\Requests\Admin\Transfer\RejectTransferRequest;
use App\Http\Requests\Admin\Transfer\CancelTransferRequest;
use App\Http\Requests\Admin\Transfer\RetryTransferRequest;
use App\Http\Resources\Admin\TransferCollection;
use App\Http\Resources\Admin\TransferResource;
use App\Models\Transfer;
use App\Services\Admin\TransferService;

class TransferController extends AdminController
{
    public function __construct(
        protected TransferService $service
    ) {
    }

    /**
     * List Transfers
     */
    public function index(
        IndexTransferRequest $request
    )
    {
        return new TransferCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Transfer Details
     */
    public function show(
        Transfer $transfer
    )
    {
        return $this->success(

            new TransferResource(

                $this->service->show(
                    $transfer
                )

            )

        );
    }

    /**
     * Approve Transfer
     */
    public function approve(
        ApproveTransferRequest $request,
        Transfer $transfer
    )
    {
        $transfer = $this->service->approve(

            $transfer,

            $request->validated()

        );

        return $this->success(

            new TransferResource(
                $transfer
            ),

            'Transfer approved successfully.'

        );
    }

    /**
     * Reject Transfer
     */
    public function reject(
        RejectTransferRequest $request,
        Transfer $transfer
    )
    {
        $transfer = $this->service->reject(

            $transfer,

            $request->validated()

        );

        return $this->success(

            new TransferResource(
                $transfer
            ),

            'Transfer rejected successfully.'

        );
    }

    /**
     * Cancel Transfer
     */
    public function cancel(
        CancelTransferRequest $request,
        Transfer $transfer
    )
    {
        $transfer = $this->service->cancel(

            $transfer,

            $request->validated()

        );

        return $this->success(

            new TransferResource(
                $transfer
            ),

            'Transfer cancelled successfully.'

        );
    }

    /**
     * Retry Transfer
     */
    public function retry(
        RetryTransferRequest $request,
        Transfer $transfer
    )
    {
        $transfer = $this->service->retry(

            $transfer,

            $request->validated()

        );

        return $this->success(

            new TransferResource(
                $transfer
            ),

            'Transfer queued for retry.'

        );
    }

    /**
     * Transfer Statistics
     */
    public function statistics()
    {
        return $this->success(

            $this->service->statistics()

        );
    }
}