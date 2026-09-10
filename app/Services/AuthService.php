<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Register User
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'first_name'     => $data['first_name'],
                'last_name'      => $data['last_name'],
                'username'       => $data['username'],
                'email'          => $data['email'],
                'phone'          => $data['phone'],
                'country'        => $data['country'],
                'password'       => Hash::make($data['password']),
                'referral_code'  => $this->generateReferralCode(),
                'is_verified'    => true, // Temporary (OTP disabled)
                'is_active'      => true,
            ]);

            Wallet::create([
                'user_id'        => $user->id,
                'wallet_number'  => $this->generateWalletNumber(),
                'balance'        => 0.00,
                'currency'       => $this->defaultCurrency($user->country),
                'is_active'      => true,
            ]);

            $device = $data['device_name'] ?? 'Tunko Web';

            $token = $user
                ->createToken($device)
                ->plainTextToken;

            return [
                'user'  => $user->fresh('wallet'),
                'token' => $token,
            ];
        });
    }

    /**
     * Login User
     */
    public function login(array $data): ?array
    {
        $login = trim($data['login']);

        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user) {
            return null;
        }

        if (! Hash::check($data['password'], $user->password)) {
            return null;
        }

        if (! $user->is_active) {
            return null;
        }

        $device = $data['device_name'] ?? 'Tunko Web';

        $user->tokens()
            ->where('name', $device)
            ->delete();

        $token = $user
            ->createToken($device)
            ->plainTextToken;

        return [
            'user'  => $user->load('wallet'),
            'token' => $token,
        ];
    }

    /**
     * Logout User
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Generate Wallet Number
     */
    private function generateWalletNumber(): string
    {
        do {
            $walletNumber = (string) random_int(
                1000000000,
                9999999999
            );
        } while (
            Wallet::where('wallet_number', $walletNumber)->exists()
        );

        return $walletNumber;
    }

    /**
     * Generate Referral Code
     */
    private function generateReferralCode(): string
    {
        do {
            $code = 'TNK' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (
            User::where('referral_code', $code)->exists()
        );

        return $code;
    }

    /**
     * Default Wallet Currency
     */
    private function defaultCurrency(string $country): string
    {
        return match (strtolower($country)) {
            'nigeria' => 'NGN',
            'niger',
            'mali' => 'XOF',
            'chad',
            'cameroon',
            'gabon',
            'equatorial guinea',
            'central african republic' => 'XAF',
            default => 'XAF',
        };
    }
}