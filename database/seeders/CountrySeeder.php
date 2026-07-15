<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Country::truncate();

        Country::insert([

            /*
            |--------------------------------------------------------------------------
            | Niger
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'Niger',
                'iso2' => 'NE',
                'iso3' => 'NER',
                'phone_code' => '+227',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA Franc',
                'flag' => '🇳🇪',
                'exchange_rate' => 1.000000,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Chad
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'Chad',
                'iso2' => 'TD',
                'iso3' => 'TCD',
                'phone_code' => '+235',
                'currency' => 'XAF',
                'currency_name' => 'Central African CFA Franc',
                'flag' => '🇹🇩',
                'exchange_rate' => 1.000000,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Burkina Faso
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'Burkina Faso',
                'iso2' => 'BF',
                'iso3' => 'BFA',
                'phone_code' => '+226',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA Franc',
                'flag' => '🇧🇫',
                'exchange_rate' => 1.000000,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Mali
            |--------------------------------------------------------------------------
            */
            [
                'name' => 'Mali',
                'iso2' => 'ML',
                'iso3' => 'MLI',
                'phone_code' => '+223',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA Franc',
                'flag' => '🇲🇱',
                'exchange_rate' => 1.000000,
                'is_active' => true,
            ],

        ]);
    }
}