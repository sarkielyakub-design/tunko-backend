<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('airtimes', function (Blueprint $table) {
            $table->dropColumn('country_id');
        });

        Schema::enableForeignKeyConstraints();

        Schema::table('airtimes', function (Blueprint $table) {
            $table->string('country_id', 2)
                ->after('reference');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('airtimes', function (Blueprint $table) {
            $table->dropColumn('country_id');
        });

        Schema::enableForeignKeyConstraints();

        Schema::table('airtimes', function (Blueprint $table) {
            $table->foreignId('country_id')
                ->after('reference');
        });
    }
};