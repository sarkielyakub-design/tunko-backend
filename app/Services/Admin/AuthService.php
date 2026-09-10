<?php

namespace App\Services\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(
        string $email,
        string $password
    ): array {

        $admin = Admin::where(
            'email',
            $email
        )->first();

        if (!$admin) {

            return [

                'success' => false,

                'message' => 'Invalid credentials.',

            ];

        }

        if (!Hash::check(
            $password,
            $admin->password
        )) {

            return [

                'success' => false,

                'message' => 'Invalid credentials.',

            ];

        }

        if (!$admin->status) {

            return [

                'success' => false,

                'message' => 'Admin account is disabled.',

            ];

        }

        $admin->tokens()->delete();

        $token = $admin
            ->createToken(
                'admin-token'
            )
            ->plainTextToken;

        $admin->update([
            'last_login_at' => now(),
        ]);

        return [

            'success' => true,

            'token' => $token,

            'admin' => $admin,

        ];
    }
}