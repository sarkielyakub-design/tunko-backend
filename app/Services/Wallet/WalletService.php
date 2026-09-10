<?php

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\Wallet;
use Exception;

class WalletService
{
    public function wallet(User $user): Wallet
    {
        $wallet = $user->wallet;

        if (! $wallet) {
            throw new Exception('Wallet not found.');
        }

        if (! $wallet->is_active) {
            throw new Exception('Wallet is inactive.');
        }

        return $wallet;
    }

    public function ensureBalance(
        Wallet $wallet,
        float $amount
    ): void {

        if ($wallet->balance < $amount) {

            throw new Exception(
                'Insufficient wallet balance.'
            );

        }
    }

    public function debit(
        Wallet $wallet,
        float $amount
    ): void {

        $wallet->decrement(
            'balance',
            $amount
        );
    }

    public function credit(
        Wallet $wallet,
        float $amount
    ): void {

        $wallet->increment(
            'balance',
            $amount
        );
    }
}