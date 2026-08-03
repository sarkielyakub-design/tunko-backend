<?php

namespace App\Services\Reloadly\Support;

class ReloadlyConfig
{
    public function environment(): string
    {
        return config('reloadly.environment');
    }

    public function clientId(): string
    {
        return config('reloadly.client_id');
    }

    public function clientSecret(): string
    {
        return config('reloadly.client_secret');
    }

    public function authUrl(): string
    {
        return config('reloadly.auth_url');
    }

    public function topupUrl(): string
    {
        return config('reloadly.topup_url');
    }

    public function giftcardUrl(): string
    {
        return config('reloadly.giftcard_url');
    }

    public function timeout(): int
    {
        return config('reloadly.timeout');
    }
}