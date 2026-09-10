<?php

namespace App\Services\Office;

use App\Models\OfficeStaff;
use App\Models\OfficeTransfer;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class OfficeTransferService
{
    public function index(OfficeStaff $staff, array $filters)
    {
        return OfficeTransfer::query()
            ->where('destination_office_id', $staff->office_id)
            ->with(['sender', 'destinationOffice', 'processedByStaff'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                        ->orWhere('recipient_phone', 'like', "%{$search}%")
                        ->orWhere('recipient_first_name', 'like', "%{$search}%")
                        ->orWhere('recipient_last_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function show(OfficeStaff $staff, OfficeTransfer $transfer): OfficeTransfer
    {
        if ((int) $transfer->destination_office_id !== (int) $staff->office_id) {
            throw new Exception('This transfer does not belong to your office.');
        }
        return $transfer->load(['sender', 'destinationOffice', 'destinationCountry', 'processedByStaff']);
    }

    public function updateStatus(OfficeStaff $staff, OfficeTransfer $transfer, string $status, ?string $reason = null): OfficeTransfer
    {
        return DB::transaction(function () use ($staff, $transfer, $status, $reason) {
            $transfer = OfficeTransfer::query()->lockForUpdate()->findOrFail($transfer->id);
            if ((int) $transfer->destination_office_id !== (int) $staff->office_id) {
                throw new Exception('This transfer does not belong to your office.');
            }

            $allowed = [
                'pending' => ['processing', 'cancelled', 'failed'],
                'processing' => ['completed', 'cancelled', 'failed'],
                'completed' => [],
                'cancelled' => [],
                'failed' => [],
            ];
            if (!in_array($status, $allowed[$transfer->status] ?? [], true)) {
                throw new Exception("Invalid office transfer status transition: {$transfer->status} → {$status}.");
            }

            if (in_array($status, ['cancelled', 'failed'], true)) {
                $this->refund($transfer, $reason ?: 'Office transfer cancelled by destination office.');
            }

            $transfer->status = $status;
            $transfer->processed_by_staff_id = $staff->id;
            if ($status === 'processing' && !$transfer->processing_started_at) $transfer->processing_started_at = now();
            if ($status === 'completed') $transfer->completed_at = now();
            if ($reason) $transfer->description = $reason;
            $transfer->save();

            return $transfer->fresh(['sender', 'destinationOffice', 'processedByStaff']);
        });
    }

    protected function refund(OfficeTransfer $transfer, string $reason): void
    {
        $alreadyRefunded = Transaction::query()->where('type', 'refund')->where('meta->office_transfer_id', $transfer->id)->exists();
        if ($alreadyRefunded) return;

        $wallet = Wallet::query()->where('user_id', $transfer->sender_id)->lockForUpdate()->first();
        if (!$wallet) throw new Exception('Customer wallet not found for refund.');

        $wallet->balance = round((float) $wallet->balance + (float) $transfer->total, 2);
        $wallet->save();

        Transaction::create([
            'user_id' => $transfer->sender_id,
            'reference' => 'OFR-' . strtoupper(Str::random(16)),
            'type' => 'refund',
            'amount' => $transfer->total,
            'fee' => 0,
            'total' => $transfer->total,
            'status' => 'completed',
            'description' => $reason,
            'meta' => [
                'direction' => 'credit',
                'transfer_type' => 'office_transfer_refund',
                'office_transfer_id' => $transfer->id,
                'office_transfer_reference' => $transfer->reference,
            ],
        ]);
    }
}
