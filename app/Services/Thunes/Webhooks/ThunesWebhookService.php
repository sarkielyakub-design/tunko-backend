<?php

namespace App\Services\Thunes\Webhooks;

use App\Models\DataPurchase;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class ThunesWebhookService
{
    /**
     * Process webhook payload.
     */
    public function handle(array $payload): void
    {
        DB::transaction(function () use ($payload) {

            $reference = $payload['reference'];

            $status = strtolower(
                $payload['status']
            );

            $purchase = DataPurchase::where(
                'reference',
                $reference
            )->first();

            if (!$purchase) {
                return;
            }

            $purchase->update([

                'provider_reference' =>
                    $payload['provider_reference'] ?? null,

                'provider_response' =>
                    json_encode($payload),

                'status' => $status,

            ]);

            Transaction::where(
                'reference',
                $reference
            )->update([

                'status' => $status,

            ]);

            /**
             * Refund wallet if failed
             */
            if ($status === 'failed') {

                $wallet = Wallet::where(
                    'user_id',
                    $purchase->user_id
                )->lockForUpdate()->first();

                if ($wallet) {

                    $wallet->increment(
                        'balance',
                        $purchase->amount
                    );

                }
            }

        });
    }
}