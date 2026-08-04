<?php

namespace App\Services\Database;

use Illuminate\Support\Facades\DB;

class DatabaseTransaction
{
    public function run(
        callable $callback
    )
    {
        return DB::transaction($callback);
    }
}