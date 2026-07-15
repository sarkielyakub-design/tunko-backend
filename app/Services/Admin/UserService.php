<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    /**
     * List Users
     */
    public function index(array $filters)
    {
        return User::query()

            ->with([
                'wallet',
                'kyc',
            ])

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where(
                            'first_name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'last_name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'username',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'phone',
                            'like',
                            "%{$search}%"
                        );

                    });

                }
            )

            ->when(
                $filters['country'] ?? null,
                fn ($q, $country) =>
                    $q->where(
                        'country',
                        $country
                    )
            )

            ->when(
                isset($filters['verified']),
                fn ($q) =>
                    $q->where(
                        'is_verified',
                        $filters['verified']
                    )
            )

            ->when(
                $filters['status'] ?? null,
                function ($q, $status) {

                    $q->where(
                        'is_active',
                        $status === 'active'
                    );

                }
            )

            ->orderBy(
                $filters['sort'] ?? 'created_at',
                $filters['direction'] ?? 'desc'
            )

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Show User
     */
    public function show(User $user)
    {
        return $user->load([
            'wallet',
            'kyc',
            'transactions',
            'beneficiaries',
            'cards',
        ]);
    }

    /**
     * Create User
     */
    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([

                'first_name' => $data['first_name'],

                'last_name' => $data['last_name'],

                'username' => $data['username'],

                'email' => $data['email'],

                'phone' => $data['phone'],

                'country' => $data['country'],

                'password' => Hash::make(
                    $data['password']
                ),

                'is_verified' =>
                    $data['is_verified'] ?? false,

                'is_active' =>
                    $data['is_active'] ?? true,

            ]);

            Wallet::create([

                'user_id' => $user->id,

                'wallet_number' =>
                    strtoupper(
                        Str::random(12)
                    ),

                'currency' => 'XOF',

                'balance' => 0,

            ]);

            return $user;

        });
    }

    /**
     * Update User
     */
    public function update(
        User $user,
        array $data
    ): User {

        $user->update($data);

        return $user->fresh([
            'wallet',
            'kyc',
        ]);
    }

    /**
     * Delete User
     */
    public function destroy(User $user): void
    {
        DB::transaction(function () use ($user) {

            $user->delete();

        });
    }
    /**
 * Credit Wallet
 */
public function creditWallet(
    User $user,
    float $amount,
    string $description,
    $admin
): User {

    return DB::transaction(function () use (
        $user,
        $amount,
        $description,
        $admin
    ) {

        $wallet = $user->wallet;

        $wallet->increment(
            'balance',
            $amount
        );

        $reference = 'CRD'.strtoupper(
            Str::random(12)
        );

        Transaction::create([

            'user_id' => $user->id,

            'reference' => $reference,

            'type' => 'deposit',

            'title' => 'Admin Wallet Credit',

            'description' => $description,

            'amount' => $amount,

            'currency' => $wallet->currency,

            'status' => 'completed',

        ]);

        AuditService::log(

            $admin,

            'Wallet',

            'Credit',

            "Credited {$user->full_name} wallet",

            [],

            [

                'amount' => $amount,

                'reference' => $reference,

            ]

        );

        return $user->fresh([
            'wallet',
        ]);

    });

}/**
 * Debit Wallet
 */
public function debitWallet(
    User $user,
    float $amount,
    string $description,
    $admin
): User {

    return DB::transaction(function () use (
        $user,
        $amount,
        $description,
        $admin
    ) {

        $wallet = $user->wallet;

        if ($wallet->balance < $amount) {

            throw new \Exception(
                'Insufficient wallet balance.'
            );

        }

        $wallet->decrement(
            'balance',
            $amount
        );

        $reference = 'DBT'.strtoupper(
            Str::random(12)
        );

        Transaction::create([

            'user_id' => $user->id,

            'reference' => $reference,

            'type' => 'withdraw',

            'title' => 'Admin Wallet Debit',

            'description' => $description,

            'amount' => $amount,

            'currency' => $wallet->currency,

            'status' => 'completed',

        ]);

        AuditService::log(

            $admin,

            'Wallet',

            'Debit',

            "Debited {$user->full_name} wallet",

            [],

            [

                'amount' => $amount,

                'reference' => $reference,

            ]

        );

        return $user->fresh([
            'wallet',
        ]);

    });

}
/**
 * Freeze User
 */
public function freeze(
    User $user,
    string $reason,
    $admin
): User {

    $user->update([

        'is_active' => false,

    ]);

    AuditService::log(

        $admin,

        'Users',

        'Freeze',

        $reason,

        [],

        $user->toArray()

    );

    return $user;

}/**
 * Unfreeze User
 */
public function unfreeze(
    User $user,
    string $reason,
    $admin
): User {

    $user->update([

        'is_active' => true,

    ]);

    AuditService::log(

        $admin,

        'Users',

        'Unfreeze',

        $reason,

        [],

        $user->toArray()

    );

    return $user;

}/**
 * Reset Password
 */
public function resetPassword(
    User $user,
    string $password,
    $admin
): User {

    $user->update([

        'password' => Hash::make(
            $password
        ),

    ]);

    AuditService::log(

        $admin,

        'Users',

        'Reset Password',

        "Password reset",

    );

    return $user;

}/**
 * Reset Transaction PIN
 */
public function resetPin(
    User $user,
    string $pin,
    $admin
): User {

    $user->update([

        'transaction_pin' => Hash::make(
            $pin
        ),

    ]);

    AuditService::log(

        $admin,

        'Users',

        'Reset PIN',

        "Transaction PIN reset",

    );

    return $user;

}
/**
 * User Details
 */
public function details(User $user): User
{
    return $user->load([

        'wallet',

        'kyc',

        'transactions' => function ($query) {
            $query->latest()->limit(20);
        },

        'walletTransactions' => function ($query) {
            $query->latest()->limit(20);
        },

        'beneficiaries',

        'cards',

    ]);
}

}