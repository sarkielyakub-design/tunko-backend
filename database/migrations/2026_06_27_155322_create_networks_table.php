<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('networks', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            $table->foreignId('country_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Network
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('slug')->unique();

            $table->string('code')->unique();

            $table->string('provider')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */

            $table->string('logo')->nullable();

            $table->string('color')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            $table->boolean('airtime_enabled')
                ->default(true);

            $table->boolean('data_enabled')
                ->default(true);

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
        Schema::dropIfExists('networks');
    }
};