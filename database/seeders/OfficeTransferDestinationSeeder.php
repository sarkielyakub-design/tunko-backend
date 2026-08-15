<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Office;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfficeTransferDestinationSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | COUNTRIES
        |--------------------------------------------------------------------------
        */

        $countries = [
            [
                'name' => 'Benin',
                'official_name' => 'Republic of Benin',
                'iso2' => 'BJ',
                'iso3' => 'BEN',
                'continent' => 'Africa',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA franc',
                'currency_symbol' => 'CFA',
                'phone_code' => '+229',
                'timezone' => 'Africa/Porto-Novo',
                'flag' => '🇧🇯',
            ],

            [
                'name' => 'Burkina Faso',
                'official_name' => 'Burkina Faso',
                'iso2' => 'BF',
                'iso3' => 'BFA',
                'continent' => 'Africa',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA franc',
                'currency_symbol' => 'CFA',
                'phone_code' => '+226',
                'timezone' => 'Africa/Ouagadougou',
                'flag' => '🇧🇫',
            ],

            [
                'name' => 'Côte d’Ivoire',
                'official_name' => 'Republic of Côte d’Ivoire',
                'iso2' => 'CI',
                'iso3' => 'CIV',
                'continent' => 'Africa',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA franc',
                'currency_symbol' => 'CFA',
                'phone_code' => '+225',
                'timezone' => 'Africa/Abidjan',
                'flag' => '🇨🇮',
            ],

            [
                'name' => 'Guinea',
                'official_name' => 'Republic of Guinea',
                'iso2' => 'GN',
                'iso3' => 'GIN',
                'continent' => 'Africa',
                'currency' => 'GNF',
                'currency_name' => 'Guinean franc',
                'currency_symbol' => 'FG',
                'phone_code' => '+224',
                'timezone' => 'Africa/Conakry',
                'flag' => '🇬🇳',
            ],

            [
                'name' => 'Mali',
                'official_name' => 'Republic of Mali',
                'iso2' => 'ML',
                'iso3' => 'MLI',
                'continent' => 'Africa',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA franc',
                'currency_symbol' => 'CFA',
                'phone_code' => '+223',
                'timezone' => 'Africa/Bamako',
                'flag' => '🇲🇱',
            ],

            [
                'name' => 'Niger',
                'official_name' => 'Republic of Niger',
                'iso2' => 'NE',
                'iso3' => 'NER',
                'continent' => 'Africa',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA franc',
                'currency_symbol' => 'CFA',
                'phone_code' => '+227',
                'timezone' => 'Africa/Niamey',
                'flag' => '🇳🇪',
            ],

            [
                'name' => 'Senegal',
                'official_name' => 'Republic of Senegal',
                'iso2' => 'SN',
                'iso3' => 'SEN',
                'continent' => 'Africa',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA franc',
                'currency_symbol' => 'CFA',
                'phone_code' => '+221',
                'timezone' => 'Africa/Dakar',
                'flag' => '🇸🇳',
            ],

            [
                'name' => 'Togo',
                'official_name' => 'Togolese Republic',
                'iso2' => 'TG',
                'iso3' => 'TGO',
                'continent' => 'Africa',
                'currency' => 'XOF',
                'currency_name' => 'West African CFA franc',
                'currency_symbol' => 'CFA',
                'phone_code' => '+228',
                'timezone' => 'Africa/Lome',
                'flag' => '🇹🇬',
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                [
                    'iso2' => $country['iso2'],
                ],
                array_merge(
                    $country,
                    [
                        'exchange_rate' => 1,
                        'wallet_enabled' => true,
                        'transfer_enabled' => true,
                        'airtime_enabled' => true,
                        'data_enabled' => true,
                        'kyc_required' => true,
                        'minimum_transfer' => 0,
                        'maximum_transfer' => 100000000,
                        'minimum_wallet_funding' => 0,
                        'maximum_wallet_funding' => 100000000,
                        'sort_order' => 0,
                        'is_active' => true,
                    ],
                ),
            );
        }

        /*
        |--------------------------------------------------------------------------
        | OFFICES
        |--------------------------------------------------------------------------
        |
        | These are the actual collection locations exposed by the
        | office-transfer destinations endpoint.
        |
        */

        $offices = [
            [
                'name' => 'Tunko Cotonou Office',
                'country' => 'Benin',
                'city' => 'Cotonou',
                'timezone' => 'Africa/Porto-Novo',
                'currency' => 'XOF',
            ],

            [
                'name' => 'Tunko Ouagadougou Office',
                'country' => 'Burkina Faso',
                'city' => 'Ouagadougou',
                'timezone' => 'Africa/Ouagadougou',
                'currency' => 'XOF',
            ],

            [
                'name' => 'Tunko Abidjan Office',
                'country' => 'Côte d’Ivoire',
                'city' => 'Abidjan',
                'timezone' => 'Africa/Abidjan',
                'currency' => 'XOF',
            ],

            [
                'name' => 'Tunko Conakry Office',
                'country' => 'Guinea',
                'city' => 'Conakry',
                'timezone' => 'Africa/Conakry',
                'currency' => 'GNF',
            ],

            [
                'name' => 'Tunko Bamako Office',
                'country' => 'Mali',
                'city' => 'Bamako',
                'timezone' => 'Africa/Bamako',
                'currency' => 'XOF',
            ],

            [
                'name' => 'Tunko Niamey Office',
                'country' => 'Niger',
                'city' => 'Niamey',
                'timezone' => 'Africa/Niamey',
                'currency' => 'XOF',
            ],

            [
                'name' => 'Tunko Dakar Office',
                'country' => 'Senegal',
                'city' => 'Dakar',
                'timezone' => 'Africa/Dakar',
                'currency' => 'XOF',
            ],

            [
                'name' => 'Tunko Lome Office',
                'country' => 'Togo',
                'city' => 'Lome',
                'timezone' => 'Africa/Lome',
                'currency' => 'XOF',
            ],
        ];

        foreach ($offices as $office) {
            Office::updateOrCreate(
                [
                    'name' => $office['name'],
                    'city' => $office['city'],
                    'country' => $office['country'],
                ],
                [
                    'slug' => Str::slug(
                        $office['name'] . '-' . $office['city']
                    ),

                    'state' => null,

                    'email' => null,
                    'phone' => null,
                    'whatsapp' => null,

                    'address' =>
                        $office['city'] . ', ' . $office['country'],

                    'latitude' => null,
                    'longitude' => null,

                    'timezone' =>
                        $office['timezone'],

                    'currency' =>
                        $office['currency'],

                    'is_head_office' => false,

                    'is_active' => true,

                    'sort_order' => 0,

                    'meta_title' =>
                        $office['name'],

                    'meta_description' =>
                        'Tunko Money collection office in '
                        . $office['city']
                        . ', '
                        . $office['country'],
                ],
            );
        }
    }
}