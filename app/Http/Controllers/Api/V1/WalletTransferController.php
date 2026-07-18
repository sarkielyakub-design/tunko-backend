<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyWalletRequest;
use App\Http\Requests\WalletTransferQuoteRequest;
use App\Http\Requests\WalletTransferRequest;
use App\Services\WalletTransferService;
use Illuminate\Http\Request;
use Throwable;

class WalletTransferController extends Controller
{
    public function __construct(
        protected WalletTransferService $walletTransferService
    ) {}

    /**
     * Verify recipient wallet.
     */
    public function verify(VerifyWalletRequest $request)
    {
        try {

            $recipient = $this->walletTransferService
                ->verifyRecipient(
                    $request->user(),
                    $request->validated()
                );

            return response()->json([
                'success' => true,
                'message' => 'Recipient verified successfully.',
                'data' => $recipient,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ],422);

        }
    }

    /**
     * Calculate transfer summary.
     */
    public function quote(WalletTransferQuoteRequest $request)
    {
        try {

            $quote = $this->walletTransferService
                ->quote(
                    $request->user(),
                    $request->validated()
                );

            return response()->json([
                'success'=>true,
                'data'=>$quote
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],422);

        }
    }

    /**
     * Send money.
     */
    public function send(WalletTransferRequest $request)
    {
        try {

            $transfer = $this->walletTransferService
                ->send(
                    $request->user(),
                    $request->validated()
                );

            return response()->json([
                'success'=>true,
                'message'=>'Transfer completed successfully.',
                'data'=>$transfer,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
            ],422);

        }
    }

    /**
     * Transfer history.
     */
    public function history(Request $request)
    {
        $history = $this->walletTransferService
            ->history(
                $request->user()
            );

        return response()->json([
            'success'=>true,
            'data'=>$history
        ]);
    }

    /**
     * Receipt.
     */
    public function receipt(string $reference)
    {
        try {

            $receipt = $this->walletTransferService
                ->receipt($reference);

            return response()->json([
                'success'=>true,
                'data'=>$receipt,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
            ],404);

        }
    }

    /**
     * Beneficiaries.
     */
    public function beneficiaries(Request $request)
    {
        $beneficiaries = $this->walletTransferService
            ->beneficiaries(
                $request->user()
            );

        return response()->json([
            'success'=>true,
            'data'=>$beneficiaries,
        ]);
    }
}