<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Exception;

class TransactionPinService
{
    /**
     * Verify transaction PIN.
     */
    public function verify(
        User $user,
        string $pin
    ): void {

        if (empty($user->transaction_pin)) {

            throw new Exception(
                'Transaction PIN is not set.'
            );

        }

        if (! Hash::check(
            $pin,
            $user->transaction_pin
        )) {

            throw new Exception(
                'Invalid transaction PIN.'
            );

        }
    }

    /**
     * Check whether user has PIN.
     */
    public function hasPin(
        User $user
    ): bool {

        return ! empty(
            $user->transaction_pin
        );

    }
}