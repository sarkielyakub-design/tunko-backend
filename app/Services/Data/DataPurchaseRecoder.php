<?php

namespace App\Services\Data;

use App\Models\DataPurchase;

class DataPurchaseRecorder
{
    public function create(array $data): DataPurchase
    {
        return DataPurchase::create($data);
    }
}