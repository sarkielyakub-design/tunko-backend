<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('card_holder');

            $table->string('card_number');

            $table->string('last_four');

            $table->string('brand');

            $table->string('expiry_month');

            $table->string('expiry_year');

            $table->boolean('is_default')
                ->default(false);

            $table->boolean('is_frozen')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};