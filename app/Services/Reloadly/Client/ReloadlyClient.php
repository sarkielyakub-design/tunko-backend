<?php

namespace App\Services\Reloadly\Http;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Services\Reloadly\Auth\ReloadlyAuthService;
use App\Services\Reloadly\Support\ReloadlyConfig;

class ReloadlyHttpClient
{
    public function __construct(
        protected ReloadlyConfig $config,
        protected ReloadlyAuthService $auth
    ) {}

    /**
     * Base HTTP Client
     */
    protected function client()
{
    return Http::baseUrl(
            $this->config->topupUrl()
        )
        ->timeout(
            $this->config->timeout()
        )
        ->withHeaders([

            'Authorization' =>
                'Bearer '.$this->auth->token(),

            'Accept' =>
                'application/com.reloadly.topups-v1+json',

            'Content-Type' =>
                'application/json',

        ]);
}
    /**
     * GET Request
     */
    public function get(
        string $endpoint,
        array $query = []
    ): Response {

        return $this->client()
            ->get($endpoint, $query);

    }

    /**
     * POST Request
     */
    public function post(
        string $endpoint,
        array $payload = []
    ): Response {

        return $this->client()
            ->post($endpoint, $payload);

    }

    /**
     * PUT Request
     */
    public function put(
        string $endpoint,
        array $payload = []
    ): Response {

        return $this->client()
            ->put($endpoint, $payload);

    }

    /**
     * DELETE Request
     */
    public function delete(
        string $endpoint
    ): Response {

        return $this->client()
            ->delete($endpoint);

    }

    /**
     * Retry once after refreshing token if unauthorized.
     */
    public function retryGet(
        string $endpoint,
        array $query = []
    ): Response {

        $response = $this->get(
            $endpoint,
            $query
        );

        if ($response->status() === 401) {

            $this->auth->refresh();

            $response = $this->get(
                $endpoint,
                $query
            );

        }

        return $response;
    }

    public function retryPost(
        string $endpoint,
        array $payload = []
    ): Response {

        $response = $this->post(
            $endpoint,
            $payload
        );

        if ($response->status() === 401) {

            $this->auth->refresh();

            $response = $this->post(
                $endpoint,
                $payload
            );

        }

        return $response;
    }
   public function detectOperator(
    string $phone,
    string $countryIso
): Response {

    return $this->retryGet(
        '/operators/auto-detect',
        [
            'phone' => $phone,
            'countryCode' => strtoupper($countryIso),
        ]
    );
}
/*
|--------------------------------------------------------------------------
| Quote
|--------------------------------------------------------------------------
*/

public function quote(array $payload): Response
{
    return $this->retryPost(
        '/topups/quotes',
        $payload
    );
}
/*
|--------------------------------------------------------------------------
| Purchase
|--------------------------------------------------------------------------
*/

public function purchase(array $payload): Response
{
    return $this->retryPost(
        '/topups',
        $payload
    );
}


}