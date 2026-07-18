<?php

namespace App\Services\Reloadly\Sync;

use App\Services\Reloadly\ReloadlyClient;
use Illuminate\Support\Facades\DB;

abstract class AbstractSyncService
{
    public function __construct(
        protected readonly ReloadlyClient $client
    ) {
    }

    abstract protected function fetch(): array;

    abstract protected function persist(array $items): int;

    public function sync(): int
    {
        return DB::transaction(function () {

            $items = $this->fetch();

            return $this->persist($items);

        });
    }
}