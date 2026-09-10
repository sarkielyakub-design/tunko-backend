<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'sender' => $this->whenLoaded('sender', fn () => [
                'id' => $this->sender->id,
                'name' => $this->sender->full_name,
                'phone' => $this->sender->phone,
            ]),
            'source_office' => $this->whenLoaded('sourceOffice', fn () => [
                'id' => $this->sourceOffice->id,
                'name' => $this->sourceOffice->name,
                'country' => $this->sourceOffice->country,
                'city' => $this->sourceOffice->city,
            ]),
            'destination_office' => $this->whenLoaded('destinationOffice', fn () => $this->destinationOffice ? [
                'id' => $this->destinationOffice->id,
                'name' => $this->destinationOffice->name,
                'country' => $this->destinationOffice->country,
                'state' => $this->destinationOffice->state,
                'city' => $this->destinationOffice->city,
                'address' => $this->destinationOffice->address,
                'phone' => $this->destinationOffice->phone,
            ] : null),
            'recipient' => [
                'first_name' => $this->recipient_first_name,
                'last_name' => $this->recipient_last_name,
                'name' => trim($this->recipient_first_name . ' ' . $this->recipient_last_name),
                'phone' => $this->recipient_phone,
            ],
            'amount' => (float) $this->amount,
            'fee' => (float) $this->fee,
            'total' => (float) $this->total,
            'currency' => $this->currency,
            'fees_included' => (bool) $this->fees_included,
            'reason' => $this->reason,
            'description' => $this->description,
            'processed_by' => $this->whenLoaded('processedByStaff', fn () => $this->processedByStaff ? [
                'id' => $this->processedByStaff->id,
                'name' => $this->processedByStaff->name,
                'role' => $this->processedByStaff->role,
            ] : null),
            'processing_started_at' => optional($this->processing_started_at)->toDateTimeString(),
            'completed_at' => optional($this->completed_at)->toDateTimeString(),
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
