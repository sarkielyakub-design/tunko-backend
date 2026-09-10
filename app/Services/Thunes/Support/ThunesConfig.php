<?php

namespace App\Services\Thunes\Support;

class ThunesConfig
{
    public function baseUrl(): string
    {
        return config('thunes.base_url');
    }

    public function apiKey(): string
    {
        return config('thunes.api_key');
    }

    public function apiSecret(): string
    {
        return config('thunes.api_secret');
    }

    public function timeout(): int
    {
        return config('thunes.timeout');
    }

    public function retry(): int
    {
        return config('thunes.retry');
    }

    public function webhookSecret(): string
    {
        return config('thunes.webhook_secret');
    }
}