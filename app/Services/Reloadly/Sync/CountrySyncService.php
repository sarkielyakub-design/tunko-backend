<?php

namespace App\Services\Reloadly\Sync;

use App\Services\Reloadly\ReloadlyClient;

class CountrySyncService extends AbstractSyncService
{
    protected function fetch(): array
    {
        return $this->client
            ->countries()
            ->json();
    }

    protected function persist(array $countries): int
    {
        // updateOrCreate logic
    }
}