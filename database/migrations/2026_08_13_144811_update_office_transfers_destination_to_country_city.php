<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_transfers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Destination Country
            |--------------------------------------------------------------------------
            */

            $table->foreignId('destination_country_id')
                ->nullable()
                ->after('sender_id')
                ->constrained('countries')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Destination City
            |--------------------------------------------------------------------------
            */

            $table->string('destination_city')
                ->nullable()
                ->after('destination_country_id');

            $table->index('destination_country_id');
            $table->index('destination_city');

        });
    }

    public function down(): void
    {
        Schema::table('office_transfers', function (Blueprint $table) {

            $table->dropForeign([
                'destination_country_id',
            ]);

            $table->dropIndex([
                'destination_country_id',
            ]);

            $table->dropIndex([
                'destination_city',
            ]);

            $table->dropColumn([
                'destination_country_id',
                'destination_city',
            ]);
        });
    }
};