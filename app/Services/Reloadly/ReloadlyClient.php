<?php

namespace App\Services\Reloadly;

use Illuminate\Http\Client\Response;

class ReloadlyClient
{
    public function __construct(
        private readonly ReloadlyHttpClient $http
    ) {
    }

    /**
     * Countries
     */
    public function countries(): Response
    {
        return $this->http->get('/countries');
    }

    /**
     * Operators by Country
     */
    public function operators(
        int $countryId,
        bool $includeBundles = true
    ): Response {

        return $this->http->get(
            '/operators/countries/' . $countryId,
            [
                'includeBundles' => $includeBundles,
            ]
        );
    }

    /**
     * Detect operator from phone number
     */
    public function detectOperator(
        string $phone,
        string $countryCode
    ): Response {

        return $this->http->get(
            '/operators/auto-detect/phone/' . $phone,
            [
                'countryCode' => $countryCode,
            ]
        );
    }

    /**
     * Operator details
     */
    public function operator(
        int $operatorId
    ): Response {

        return $this->http->get(
            '/operators/' . $operatorId
        );
    }

    /**
     * Airtime Topup
     */
    public function topup(
        array $payload
    ): Response {

        return $this->http->post(
            '/topups',
            $payload
        );
    }
}