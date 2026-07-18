<?php

namespace App\Services\Thunes\Auth;

use App\Services\Thunes\Support\ThunesConfig;

class ThunesAuthService
{
    public function __construct(
        private readonly ThunesConfig $config
    ) {}

    public function headers(): array
    {
        return [

            'Authorization' => 'Bearer '.$this->config->apiKey(),

            'Accept' => 'application/json',

            'Content-Type' => 'application/json',

        ];
    }
}