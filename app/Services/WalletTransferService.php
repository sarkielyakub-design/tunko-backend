<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class WalletTransferService
{
    /**
     * Verify recipient.
     */
    public function verifyRecipient(User $sender, array $data): array
    {
        $recipient = User::query()
            ->where('email', $data['recipient'])
            ->orWhere('phone', $data['recipient'])
            ->orWhere('username', $data['recipient'])
            ->orWhereHas('wallet', function ($query) use ($data) {
                $query->where('wallet_number', $data['recipient']);
            })
            ->with('wallet')
            ->first();

        if (!$recipient) {
            throw new Exception('Recipient not found.');
        }

        if ($recipient->id === $sender->id) {
            throw new Exception('You cannot transfer to yourself.');
        }

        if (!$recipient->wallet || !$recipient->wallet->is_active) {
            throw new Exception('Recipient wallet is unavailable.');
        }

        return [
            'id' => $recipient->id,
            'name' => trim($recipient->first_name . ' ' . $recipient->last_name),
            'wallet_number' => $recipient->wallet->wallet_number,
            'currency' => $recipient->wallet->currency,
        ];
    }

    /**
     * Generate transfer quote.
     */
    public function quote(User $sender, array $data): array
    {
        $recipient = $this->verifyRecipient($sender, [
            'recipient' => $data['recipient']
        ]);

        $amount = (float) $data['amount'];

        $fee = 0;

        $total = $amount + $fee;

        return [
            'recipient' => $recipient,
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'currency' => $sender->wallet->currency,
        ];
    }

    /**
     * Send money.
     */
public function send(User $sender, array $data): array
{
    $sender->load('wallet');

    if (!$sender->wallet) {
        throw new Exception('Sender wallet not found.');
    }

    if (!$sender->wallet->is_active) {
        throw new Exception('Your wallet is inactive.');
    }

    if (
        empty($sender->transaction_pin) ||
        !Hash::check($data['pin'], $sender->transaction_pin)
    ) {
        throw new Exception('Invalid transaction PIN.');
    }

    $recipient = User::query()
        ->where('email', $data['recipient'])
        ->orWhere('phone', $data['recipient'])
        ->orWhere('username', $data['recipient'])
        ->orWhereHas('wallet', function ($query) use ($data) {
            $query->where('wallet_number', $data['recipient']);
        })
        ->with('wallet')
        ->first();

    if (!$recipient) {
        throw new Exception('Recipient not found.');
    }

    if ($recipient->id === $sender->id) {
        throw new Exception('You cannot transfer to yourself.');
    }

    if (!$recipient->wallet) {
        throw new Exception('Recipient wallet not found.');
    }

    if (!$recipient->wallet->is_active) {
        throw new Exception('Recipient wallet is inactive.');
    }

    $amount = (float)$data['amount'];

    if ($amount <= 0) {
        throw new Exception('Invalid amount.');
    }

    $fee = 0;
    $total = $amount + $fee;

    if ($sender->wallet->balance < $total) {
        throw new Exception('Insufficient wallet balance.');
    }

    $reference = $this->generateReference();

    return DB::transaction(function () use (
        $sender,
        $recipient,
        $amount,
        $fee,
        $total,
        $reference,
        $data
    ) {

        $senderWallet = Wallet::lockForUpdate()->findOrFail(
            $sender->wallet->id
        );

        $recipientWallet = Wallet::lockForUpdate()->findOrFail(
            $recipient->wallet->id
        );

        if ($senderWallet->balance < $total) {
            throw new Exception('Insufficient wallet balance.');
        }

        // Debit sender
        $senderWallet->balance -= $total;
        $senderWallet->save();

        // Credit recipient
        $recipientWallet->balance += $amount;
        $recipientWallet->save();

        // Transfer record
        $transfer = WalletTransfer::create([
            'reference' => $reference,
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'sender_wallet_id' => $senderWallet->id,
            'recipient_wallet_id' => $recipientWallet->id,
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'currency' => $senderWallet->currency,
            'status' => 'completed',
            'description' => $data['description'] ?? null,
            'completed_at' => now(),
        ]);

        // Sender transaction
        Transaction::create([
            'user_id' => $sender->id,
            'reference' => $reference,
            'type' => 'transfer',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'status' => 'completed',
            'description' => 'Transfer to '.$recipient->first_name.' '.$recipient->last_name,
            'meta' => [
                'wallet_id' => $senderWallet->id,
                'direction' => 'debit',
                'recipient_id' => $recipient->id,
            ],
        ]);

        // Recipient transaction
        Transaction::create([
            'user_id' => $recipient->id,
            'reference' => $reference,
            'type' => 'transfer',
            'amount' => $amount,
            'fee' => 0,
            'total' => $amount,
            'status' => 'completed',
            'description' => 'Transfer from '.$sender->first_name.' '.$sender->last_name,
            'meta' => [
                'wallet_id' => $recipientWallet->id,
                'direction' => 'credit',
                'sender_id' => $sender->id,
            ],
        ]);

        $senderWallet->refresh();
        $recipientWallet->refresh();

        return [
            'reference' => $reference,
            'status' => 'completed',
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total,
            'currency' => $senderWallet->currency,
            'sender_balance' => $senderWallet->balance,
            'recipient_balance' => $recipientWallet->balance,
            'recipient' => [
                'id' => $recipient->id,
                'name' => trim($recipient->first_name.' '.$recipient->last_name),
                'wallet_number' => $recipientWallet->wallet_number,
            ],
            'completed_at' => $transfer->completed_at,
        ];
    });
}
    /**
     * History.
     */
    public function history(User $user)
    {
        return WalletTransfer::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->orWhere('recipient_id', $user->id);
        })
        ->latest()
        ->paginate(20);
    }

    /**
     * Receipt.
     */
    public function receipt(string $reference): array
{
    $transfer = WalletTransfer::with([
        'sender.wallet',
        'recipient.wallet',
    ])->where('reference', $reference)->firstOrFail();

    return [

        'reference' => $transfer->reference,

        'status' => $transfer->status,

        'date' => optional($transfer->completed_at)
            ->format('d M Y H:i'),

        'description' => $transfer->description,

        'amount' => (double)$transfer->amount,

        'fee' => (double)$transfer->fee,

        'total' => (double)$transfer->total,

        'currency' => $transfer->currency,

        'sender' => [

            'id' => $transfer->sender->id,

            'name' => trim(
                $transfer->sender->first_name .
                ' ' .
                $transfer->sender->last_name
            ),

            'phone' => $transfer->sender->phone,

            'wallet_number' => optional(
                $transfer->sender->wallet
            )->wallet_number,

        ],

        'recipient' => [

            'id' => $transfer->recipient->id,

            'name' => trim(
                $transfer->recipient->first_name .
                ' ' .
                $transfer->recipient->last_name
            ),

            'phone' => $transfer->recipient->phone,

            'wallet_number' => optional(
                $transfer->recipient->wallet
            )->wallet_number,

        ],

    ];
}

    /**
     * Beneficiaries.
     */
    public function beneficiaries(User $user)
    {
        return WalletTransfer::where('sender_id', $user->id)
            ->with('recipient')
            ->latest()
            ->take(20)
            ->get()
            ->unique('recipient_id')
            ->values();
    }

    /**
     * Generate reference.
     */
    protected function generateReference(): string
    {
        return 'WT' .
            Carbon::now()->format('YmdHis') .
            strtoupper(Str::random(4));
    }
    
}