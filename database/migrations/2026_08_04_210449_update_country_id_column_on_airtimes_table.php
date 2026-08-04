<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('airtimes', function (Blueprint $table) {
            $table->string('country_id', 5)->change();
        });
    }

    public function down(): void
    {
        Schema::table('airtimes', function (Blueprint $table) {
            $table->unsignedBigInteger('country_id')->change();
        });
    }
};