<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Current schema already contains country_code.
        |
        | Remove the old country_id foreign key/column safely.
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasTable('airtimes')) {
            return;
        }

        if (Schema::hasColumn('airtimes', 'country_id')) {

            Schema::table('airtimes', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });

            Schema::table('airtimes', function (Blueprint $table) {
                $table->dropColumn('country_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('airtimes')) {
            return;
        }

        if (!Schema::hasColumn('airtimes', 'country_id')) {

            Schema::table('airtimes', function (Blueprint $table) {
                $table->foreignId('country_id')
                    ->nullable()
                    ->after('reference');
            });

            Schema::table('airtimes', function (Blueprint $table) {
                $table->foreign('country_id')
                    ->references('id')
                    ->on('countries')
                    ->nullOnDelete();
            });
        }
    }
};