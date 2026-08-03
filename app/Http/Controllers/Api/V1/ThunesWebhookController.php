<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Thunes\Webhooks\ThunesWebhookService;

class ThunesWebhookController extends Controller
{
    public function __construct(
        private readonly ThunesWebhookService $service
    ) {}

    public function handle(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-Thunes-Signature');

        abort_unless(
            $this->service->verifySignature(
                $signature,
                $request->getContent()
            ),
            401,
            'Invalid webhook signature.'
        );

        // Process webhook
        $this->service->handle(
            $request->all()
        );

        return response()->json([
            'success' => true,
        ]);
    }
    public function verifySignature(
    ?string $signature,
    string $payload
): bool {

    if (!$signature) {
        return false;
    }

    $expected = hash_hmac(
        'sha256',
        $payload,
        config('thunes.webhook_secret')
    );

    return hash_equals(
        $expected,
        $signature
    );
}
}