<?php

namespace App\Services;

class AirtimeService
{
    public function purchase(array $data): array
    {
        /**
         * Later this will call:
         *
         * Reloadly
         * Ding
         * DT One
         * etc.
         */

        return [

            "success" => true,

            "provider_reference" => null,

            "message" => "Airtime purchase successful.",

        ];
    }
}