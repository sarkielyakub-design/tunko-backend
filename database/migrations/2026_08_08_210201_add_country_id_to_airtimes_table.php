<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airtimes', function (Blueprint $table) {
            $table->foreignId('country_id')
                ->nullable()
                ->after('reference')
                ->constrained('countries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('airtimes', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};