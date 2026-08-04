<?php

namespace App\Services\Reference;

use Illuminate\Support\Str;

class ReferenceService
{
    public function generate(
        string $prefix = 'TXN'
    ): string {

        return sprintf(
            '%s%s%s',
            strtoupper($prefix),
            now()->format('YmdHis'),
            strtoupper(Str::random(6))
        );
    }
}