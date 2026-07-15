<?php

namespace Database\Seeders;

use App\Models\DataBundle;
use App\Models\Network;
use Illuminate\Database\Seeder;

class DataBundleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DataBundle::truncate();

        $plans = [

            [
                'name' => '500 MB',
                'volume' => '500 MB',
                'amount' => 500,
                'validity' => '1 Day',
            ],

            [
                'name' => '1 GB',
                'volume' => '1 GB',
                'amount' => 1000,
                'validity' => '3 Days',
            ],

            [
                'name' => '2 GB',
                'volume' => '2 GB',
                'amount' => 2000,
                'validity' => '7 Days',
            ],

            [
                'name' => '5 GB',
                'volume' => '5 GB',
                'amount' => 5000,
                'validity' => '30 Days',
            ],

            [
                'name' => '10 GB',
                'volume' => '10 GB',
                'amount' => 10000,
                'validity' => '30 Days',
            ],

            [
                'name' => '20 GB',
                'volume' => '20 GB',
                'amount' => 18000,
                'validity' => '30 Days',
            ],

            [
                'name' => '50 GB',
                'volume' => '50 GB',
                'amount' => 40000,
                'validity' => '60 Days',
            ],

        ];

        $networks = Network::all();

        foreach ($networks as $network) {

            foreach ($plans as $plan) {

                DataBundle::create([

                    'network_id' => $network->id,

                    'name' => $plan['name'],

                    'volume' => $plan['volume'],

                    'amount' => $plan['amount'],

                    'currency' => in_array(
                        $network->country->code,
                        ['NE', 'BF', 'ML']
                    )
                        ? 'XOF'
                        : 'XAF',

                    'validity' => $plan['validity'],

                    'provider_bundle_id' => null,

                    'is_active' => true,

                ]);
            }
        }
    }
}