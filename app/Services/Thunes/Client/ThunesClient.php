<?php

namespace App\Services\Thunes\Client;

use App\Services\Thunes\Http\ThunesHttpClient;

class ThunesClient
{
    public function __construct(
        private readonly ThunesHttpClient $http
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Health Check
    |--------------------------------------------------------------------------
    */

    public function health()
    {
        return $this->http->get('/');
    }
}