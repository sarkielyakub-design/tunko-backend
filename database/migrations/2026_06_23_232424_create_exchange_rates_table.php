<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('exchange_rates', function (Blueprint $table) {

    $table->id();

    $table->foreignId('from_country_id')
        ->constrained('countries')
        ->cascadeOnDelete();

    $table->foreignId('to_country_id')
        ->constrained('countries')
        ->cascadeOnDelete();

    $table->string('from_currency',3);

    $table->string('to_currency',3);

    $table->decimal('buy_rate',18,6);

    $table->decimal('sell_rate',18,6);

    $table->decimal('fee',18,2)->default(0);

    $table->decimal('minimum_amount',18,2)->default(0);

    $table->decimal('maximum_amount',18,2)->default(100000000);

    $table->boolean('is_active')->default(true);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
