<?php

namespace App\Services\CinetPay\Client;

use App\Services\CinetPay\Config\CinetPayConfig;
use App\Services\CinetPay\Http\CinetPayHttpClient;

class CinetPayClient
{
    public function __construct(
        protected CinetPayHttpClient $http,
        protected CinetPayConfig $config
    ) {}

    /**
     * Initialize Payment
     */
    public function initialize(array $data): array
    {
        $response = $this->http->post(
            '/payment',
            array_merge($data, [

                'apikey' => $this->config->apiKey(),

                'site_id' => $this->config->siteId(),

                'currency' => $this->config->currency(),

                'lang' => $this->config->language(),

                'notify_url' => $this->config->notifyUrl(),

                'return_url' => $this->config->returnUrl(),

            ])
        );

        return $response->json();
    }

    /**
     * Verify Transaction
     */
    public function verify(
        string $transactionId
    ): array {

        $response = $this->http->post(
            '/payment/check',
            [

                'apikey' => $this->config->apiKey(),

                'site_id' => $this->config->siteId(),

                'transaction_id' => $transactionId,

            ]
        );

        return $response->json();
    }
}