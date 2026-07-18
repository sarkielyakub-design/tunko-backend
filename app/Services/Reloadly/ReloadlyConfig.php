<?php

namespace App\Services\Reloadly;

class ReloadlyConfig
{
    public function clientId(): string
    {
        return config('reloadly.client_id');
    }

    public function clientSecret(): string
    {
        return config('reloadly.client_secret');
    }

    public function audience(): string
    {
        return config('reloadly.audience');
    }

    public function authUrl(): string
    {
        return config('reloadly.auth_url');
    }

    public function topupUrl(): string
    {
        return config('reloadly.topup_url');
    }

    public function timeout(): int
    {
        return config('reloadly.timeout');
    }
}