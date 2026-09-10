<?php

namespace App\Services\CinetPay\Config;

class CinetPayConfig
{
    public function baseUrl(): string
    {
        return config('cinetpay.base_url');
    }

    public function siteId(): string
    {
        return config('cinetpay.site_id');
    }

    public function apiKey(): string
    {
        return config('cinetpay.api_key');
    }

    public function secretKey(): string
    {
        return config('cinetpay.secret_key');
    }

    public function currency(): string
    {
        return config('cinetpay.currency');
    }

    public function language(): string
    {
        return config('cinetpay.language');
    }

    public function returnUrl(): string
    {
        return config('cinetpay.return_url');
    }

    public function notifyUrl(): string
    {
        return config('cinetpay.notify_url');
    }
}