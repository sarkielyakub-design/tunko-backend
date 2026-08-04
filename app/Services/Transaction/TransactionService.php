<?php

namespace App\Services\Transaction;

use App\Models\Transaction;

class TransactionService
{
    public function create(
        array $data
    ): Transaction {

        return Transaction::create($data);

    }
}