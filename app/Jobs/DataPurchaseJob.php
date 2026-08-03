<?php

namespace App\Jobs;

use App\Models\DataPurchase;
use App\Services\Thunes\Purchase\ThunesPurchaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DataPurchaseJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public function __construct(
        public int $purchaseId
    ) {}

    public function handle(
        ThunesPurchaseService $service
    ): void {

        $purchase = DataPurchase::findOrFail(
            $this->purchaseId
        );

        if ($purchase->status != 'pending') {
            return;
        }

        $response = $service->purchase([

            'reference' => $purchase->reference,

            'recipient' => $purchase->phone,

            'operator' => $purchase->network_id,

            'product' => $purchase->bundle_id,

            'amount' => $purchase->amount,

        ]);

        $purchase->update([

            'provider_reference' =>
                $response['id'] ?? null,

            'provider_response' =>
                json_encode($response),

            'status' => 'processing',

        ]);

    }
}