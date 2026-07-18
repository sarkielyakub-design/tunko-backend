<?php

namespace App\Contracts;

interface ProviderInterface
{
    public function name(): string;

    public function priority(): int;

    public function available(): bool;
}