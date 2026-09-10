<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Wallet\IndexWalletRequest;
use App\Http\Requests\Admin\Wallet\CreditWalletRequest;
use App\Http\Requests\Admin\Wallet\DebitWalletRequest;
use App\Http\Requests\Admin\Wallet\FreezeWalletRequest;
use App\Http\Requests\Admin\Wallet\UnfreezeWalletRequest;
use App\Http\Resources\Admin\WalletCollection;
use App\Http\Resources\Admin\WalletResource;
use App\Models\Wallet;
use App\Services\Admin\WalletService;

class WalletController extends AdminController
{
    public function __construct(
        protected WalletService $service
    ) {
    }

    /**
     * List Wallets
     */
    public function index(
        IndexWalletRequest $request
    )
    {
        return new WalletCollection(

            $this->service->index(
                $request->validated()
            )

        );
    }

    /**
     * Wallet Details
     */
    public function show(
        Wallet $wallet
    )
    {
        return $this->success(

            new WalletResource(

                $this->service->show(
                    $wallet
                )

            )

        );
    }

    /**
     * Credit Wallet
     */
    public function credit(
        CreditWalletRequest $request,
        Wallet $wallet
    )
    {
        $wallet = $this->service->credit(

            $wallet,

            $request->validated()

        );

        return $this->success(

            new WalletResource($wallet),

            'Wallet credited successfully.'

        );
    }

    /**
     * Debit Wallet
     */
    public function debit(
        DebitWalletRequest $request,
        Wallet $wallet
    )
    {
        $wallet = $this->service->debit(

            $wallet,

            $request->validated()

        );

        return $this->success(

            new WalletResource($wallet),

            'Wallet debited successfully.'

        );
    }

    /**
     * Freeze Wallet
     */
    public function freeze(
        FreezeWalletRequest $request,
        Wallet $wallet
    )
    {
        $wallet = $this->service->freeze(

            $wallet,

            $request->validated()

        );

        return $this->success(

            new WalletResource($wallet),

            'Wallet frozen successfully.'

        );
    }

    /**
     * Unfreeze Wallet
     */
    public function unfreeze(
        UnfreezeWalletRequest $request,
        Wallet $wallet
    )
    {
        $wallet = $this->service->unfreeze(
            $wallet
        );

        return $this->success(

            new WalletResource($wallet),

            'Wallet unfrozen successfully.'

        );
    }

    /**
     * Wallet Statement
     */
    public function statement(
        Wallet $wallet
    )
    {
        return $this->success(

            $this->service->statement(
                $wallet
            )

        );
    }

    /**
     * Wallet Transactions
     */
    public function transactions(
        Wallet $wallet
    )
    {
        return $this->success(

            $this->service->transactions(
                $wallet
            )

        );
    }

    /**
     * Wallet Summary
     */
    public function summary()
    {
        return $this->success(

            $this->service->summary()

        );
    }
}