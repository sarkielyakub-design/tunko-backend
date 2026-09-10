<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id'=>$this->id,

            'name'=>$this->name,

            'iso2'=>$this->iso2,

            'currency'=>$this->currency,

            'currency_name'=>$this->currency_name,

            'flag'=>$this->flag,

            'exchange_rate'=>$this->exchange_rate,

        ];
    }
}