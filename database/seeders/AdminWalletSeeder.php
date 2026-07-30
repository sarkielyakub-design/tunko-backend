<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminWallet;

class AdminWalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminWallet::updateOrCreate(
            [
                'wallet_number' => 'TNK-XAF-001',
            ],
            [
                'wallet_name' => 'Tunko Main Wallet',
                'currency' => 'XAF',
                'balance' => 10000000.00,
                'status' => 'active',
                'description' => 'Main settlement wallet for manual deposits',
            ]
        );
    }
}