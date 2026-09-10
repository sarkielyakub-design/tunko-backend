<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Network;
use Illuminate\Database\Seeder;

class NetworkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Network::truncate();

        /*
        |--------------------------------------------------------------------------
        | Countries
        |--------------------------------------------------------------------------
        */

        $niger = Country::where('iso2', 'NE')->firstOrFail();
        $chad = Country::where('iso2', 'TD')->firstOrFail();
        $burkina = Country::where('iso2', 'BF')->firstOrFail();
        $mali = Country::where('iso2', 'ML')->firstOrFail();

        Network::insert([

            /*
            |--------------------------------------------------------------------------
            | Niger
            |--------------------------------------------------------------------------
            */

            [
                'country_id' => $niger->id,
                'name' => 'Airtel Niger',
                'code' => 'AIRTEL_NE',
                'logo' => 'airtel.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            [
                'country_id' => $niger->id,
                'name' => 'Moov Africa Niger',
                'code' => 'MOOV_NE',
                'logo' => 'moov.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Chad
            |--------------------------------------------------------------------------
            */

            [
                'country_id' => $chad->id,
                'name' => 'Airtel Chad',
                'code' => 'AIRTEL_TD',
                'logo' => 'airtel.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            [
                'country_id' => $chad->id,
                'name' => 'Moov Africa Chad',
                'code' => 'MOOV_TD',
                'logo' => 'moov.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Burkina Faso
            |--------------------------------------------------------------------------
            */

            [
                'country_id' => $burkina->id,
                'name' => 'Orange Burkina',
                'code' => 'ORANGE_BF',
                'logo' => 'orange.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            [
                'country_id' => $burkina->id,
                'name' => 'Moov Africa Burkina',
                'code' => 'MOOV_BF',
                'logo' => 'moov.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            [
                'country_id' => $burkina->id,
                'name' => 'Telecel Burkina',
                'code' => 'TELECEL_BF',
                'logo' => 'telecel.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            /*
            |--------------------------------------------------------------------------
            | Mali
            |--------------------------------------------------------------------------
            */

            [
                'country_id' => $mali->id,
                'name' => 'Orange Mali',
                'code' => 'ORANGE_ML',
                'logo' => 'orange.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            [
                'country_id' => $mali->id,
                'name' => 'Moov Africa Mali',
                'code' => 'MOOV_ML',
                'logo' => 'moov.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

            [
                'country_id' => $mali->id,
                'name' => 'Malitel',
                'code' => 'MALITEL_ML',
                'logo' => 'malitel.png',
                'supports_airtime' => true,
                'supports_data' => true,
                'is_active' => true,
            ],

        ]);
    }
}