<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            "id" => $this->id,

            "module" => $this->module,

            "action" => $this->action,

            "description" => $this->description,

            "ip_address" => $this->ip_address,

            "created_at" => $this->created_at,

            "admin" => [

                "id" => $this->admin?->id,

                "name" => $this->admin?->name,

                "email" => $this->admin?->email,

            ],

        ];
    }
}