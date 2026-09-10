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
            /*
|--------------------------------------------------------------------------
| Nigeria
|--------------------------------------------------------------------------
*/
[
    'name' => 'Nigeria',
    'iso2' => 'NG',
    'iso3' => 'NGA',
    'phone_code' => '+234',
    'currency' => 'NGN',
    'currency_name' => 'Nigerian Naira',
    'flag' => '🇳🇬',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Ghana
|--------------------------------------------------------------------------
*/
[
    'name' => 'Ghana',
    'iso2' => 'GH',
    'iso3' => 'GHA',
    'phone_code' => '+233',
    'currency' => 'GHS',
    'currency_name' => 'Ghana Cedi',
    'flag' => '🇬🇭',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Benin
|--------------------------------------------------------------------------
*/
[
    'name' => 'Benin',
    'iso2' => 'BJ',
    'iso3' => 'BEN',
    'phone_code' => '+229',
    'currency' => 'XOF',
    'currency_name' => 'West African CFA Franc',
    'flag' => '🇧🇯',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Togo
|--------------------------------------------------------------------------
*/
[
    'name' => 'Togo',
    'iso2' => 'TG',
    'iso3' => 'TGO',
    'phone_code' => '+228',
    'currency' => 'XOF',
    'currency_name' => 'West African CFA Franc',
    'flag' => '🇹🇬',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Ivory Coast
|--------------------------------------------------------------------------
*/
[
    'name' => 'Côte d’Ivoire',
    'iso2' => 'CI',
    'iso3' => 'CIV',
    'phone_code' => '+225',
    'currency' => 'XOF',
    'currency_name' => 'West African CFA Franc',
    'flag' => '🇨🇮',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Senegal
|--------------------------------------------------------------------------
*/
[
    'name' => 'Senegal',
    'iso2' => 'SN',
    'iso3' => 'SEN',
    'phone_code' => '+221',
    'currency' => 'XOF',
    'currency_name' => 'West African CFA Franc',
    'flag' => '🇸🇳',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Guinea
|--------------------------------------------------------------------------
*/
[
    'name' => 'Guinea',
    'iso2' => 'GN',
    'iso3' => 'GIN',
    'phone_code' => '+224',
    'currency' => 'GNF',
    'currency_name' => 'Guinean Franc',
    'flag' => '🇬🇳',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Guinea-Bissau
|--------------------------------------------------------------------------
*/
[
    'name' => 'Guinea-Bissau',
    'iso2' => 'GW',
    'iso3' => 'GNB',
    'phone_code' => '+245',
    'currency' => 'XOF',
    'currency_name' => 'West African CFA Franc',
    'flag' => '🇬🇼',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Sierra Leone
|--------------------------------------------------------------------------
*/
[
    'name' => 'Sierra Leone',
    'iso2' => 'SL',
    'iso3' => 'SLE',
    'phone_code' => '+232',
    'currency' => 'SLE',
    'currency_name' => 'Sierra Leone Leone',
    'flag' => '🇸🇱',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Liberia
|--------------------------------------------------------------------------
*/
[
    'name' => 'Liberia',
    'iso2' => 'LR',
    'iso3' => 'LBR',
    'phone_code' => '+231',
    'currency' => 'LRD',
    'currency_name' => 'Liberian Dollar',
    'flag' => '🇱🇷',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Gambia
|--------------------------------------------------------------------------
*/
[
    'name' => 'Gambia',
    'iso2' => 'GM',
    'iso3' => 'GMB',
    'phone_code' => '+220',
    'currency' => 'GMD',
    'currency_name' => 'Dalasi',
    'flag' => '🇬🇲',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

/*
|--------------------------------------------------------------------------
| Cape Verde
|--------------------------------------------------------------------------
*/
[
    'name' => 'Cape Verde',
    'iso2' => 'CV',
    'iso3' => 'CPV',
    'phone_code' => '+238',
    'currency' => 'CVE',
    'currency_name' => 'Cape Verde Escudo',
    'flag' => '🇨🇻',
    'exchange_rate' => 1.000000,
    'is_active' => true,
],

        ]);
    }
}