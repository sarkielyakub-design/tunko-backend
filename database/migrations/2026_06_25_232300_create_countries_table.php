<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Country Information
            |--------------------------------------------------------------------------
            */

            $table->string('name');
            $table->string('official_name')->nullable();

            $table->string('iso2', 2)->unique();
            $table->string('iso3', 3)->unique();

            $table->string('continent')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $table->string('currency', 3);

            $table->string('currency_name');

            $table->string('currency_symbol', 10)->nullable();

            $table->decimal('exchange_rate', 15, 6)
                ->default(1);

            /*
            |--------------------------------------------------------------------------
            | Communication
            |--------------------------------------------------------------------------
            */

            $table->string('phone_code', 10);

            $table->string('timezone')
                ->default('UTC');

            $table->string('flag')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            $table->boolean('wallet_enabled')
                ->default(true);

            $table->boolean('transfer_enabled')
                ->default(true);

            $table->boolean('airtime_enabled')
                ->default(true);

            $table->boolean('data_enabled')
                ->default(true);

            $table->boolean('kyc_required')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Limits
            |--------------------------------------------------------------------------
            */

            $table->decimal('minimum_transfer', 18, 2)
                ->default(0);

            $table->decimal('maximum_transfer', 18, 2)
                ->default(100000000);

            $table->decimal('minimum_wallet_funding', 18, 2)
                ->default(0);

            $table->decimal('maximum_wallet_funding', 18, 2)
                ->default(100000000);

            /*
            |--------------------------------------------------------------------------
            | Display
            |--------------------------------------------------------------------------
            */

            $table->integer('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};