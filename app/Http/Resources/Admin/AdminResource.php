<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' => $this->id,

            'name' => $this->name,

            'email' => $this->email,

            'phone' => $this->phone,

            'avatar' => $this->avatar,

            'status' => $this->status,

            'last_login_at' => $this->last_login_at,

            'roles' => $this->getRoleNames(),

        ];
    }
}