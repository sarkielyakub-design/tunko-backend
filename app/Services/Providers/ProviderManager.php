<?php

namespace App\Services\Providers;

use App\Contracts\ProviderInterface;

class ProviderManager
{
    /**
     * @param ProviderInterface[] $providers
     */
    public function __construct(
        private iterable $providers
    ) {}

    public function provider(): ProviderInterface
    {
        foreach ($this->providers as $provider) {

            if ($provider->available()) {

                return $provider;
            }
        }

        throw new \RuntimeException(
            "No provider available."
        );
    }
}