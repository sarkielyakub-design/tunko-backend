<?php

namespace App\Services\Reloadly\Client;

use App\Services\Reloadly\Auth\ReloadlyAuthService;
use App\Services\Reloadly\Support\ReloadlyConfig;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ReloadlyClient
{
    public function __construct(
        protected ReloadlyConfig $config,
        protected ReloadlyAuthService $auth,
    ) {}

    /**
     * Base HTTP Client
     */
    protected function client()
    {
        return Http::baseUrl($this->config->topupUrl())
            ->timeout($this->config->timeout())
            ->withToken($this->auth->token())
            ->replaceHeaders([
                'Accept' => 'application/com.reloadly.topups-v1+json',
            ]);
    }

    /**
     * Execute request with automatic token refresh.
     */
    protected function request(
        string $method,
        string $endpoint,
        array $data = []
    ): Response {

        $response = $this->client()->$method($endpoint, $data);

        if ($response->status() === 401) {

            $this->auth->refresh();

            $response = $this->client()->$method($endpoint, $data);
        }

        logger()->info('Reloadly Request', [
            'method' => strtoupper($method),
            'url' => $this->config->topupUrl() . $endpoint,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Generic Methods
    |--------------------------------------------------------------------------
    */

    public function get(
        string $endpoint,
        array $query = []
    ): Response {
        return $this->request('get', $endpoint, $query);
    }

    public function post(
        string $endpoint,
        array $payload = []
    ): Response {
        return $this->request('post', $endpoint, $payload);
    }

    public function put(
        string $endpoint,
        array $payload = []
    ): Response {
        return $this->request('put', $endpoint, $payload);
    }

    public function delete(
        string $endpoint
    ): Response {
        return $this->request('delete', $endpoint);
    }

    /*
    |--------------------------------------------------------------------------
    | Countries
    |--------------------------------------------------------------------------
    */

    public function countries(): Response
    {
        return $this->get('/countries');
    }

    /*
    |--------------------------------------------------------------------------
    | Operators
    |--------------------------------------------------------------------------
    */

    public function operators(
        string $countryIso
    ): Response {

        return $this->get(
            '/operators/countries/' . strtoupper($countryIso)
        );
    }

    public function detectOperator(
        string $phone,
        string $countryIso
    ): Response {

        return $this->get(
            '/operators/auto-detect',
            [
                'phone' => $phone,
                'countryCode' => strtoupper($countryIso),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    public function products(
        int $operatorId
    ): Response {

        return $this->get(
            "/operators/{$operatorId}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Quotes
    |--------------------------------------------------------------------------
    */

    public function quote(
        array $payload
    ): Response {

        return $this->post(
            '/topups/quotes',
            $payload
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase
    |--------------------------------------------------------------------------
    */

    public function purchase(
        array $payload
    ): Response {

        return $this->post(
            '/topups',
            $payload
        );
    }
}