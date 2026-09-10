<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Transaction\IndexTransactionRequest;
use App\Http\Requests\Admin\Transaction\RefundTransactionRequest;
use App\Http\Requests\Admin\Transaction\UpdateTransactionStatusRequest;
use App\Http\Resources\Admin\TransactionCollection;
use App\Http\Resources\Admin\TransactionResource;
use App\Models\Transaction;
use App\Services\Admin\TransactionService;

class TransactionController extends AdminController
{
    public function __construct(
        protected TransactionService $service
    ) {
    }

    /**
     * List Transactions
     */
    public function index(
        IndexTransactionRequest $request
    )
    {
        return new TransactionCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Transaction Details
     */
    public function show(
        Transaction $transaction
    )
    {
        return $this->success(

            new TransactionResource(

                $this->service->show(
                    $transaction
                )

            )

        );
    }

    /**
     * Refund Transaction
     */
    public function refund(
        RefundTransactionRequest $request,
        Transaction $transaction
    )
    {
        $transaction = $this->service->refund(

            $transaction,

            $request->validated()

        );

        return $this->success(

            new TransactionResource(
                $transaction
            ),

            'Transaction refunded successfully.'

        );
    }

    /**
     * Update Status
     */
    public function updateStatus(
        UpdateTransactionStatusRequest $request,
        Transaction $transaction
    )
    {
        $transaction = $this->service->updateStatus(

            $transaction,

            $request->validated()

        );

        return $this->success(

            new TransactionResource(
                $transaction
            ),

            'Transaction updated successfully.'

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