<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('recipient_id')->nullable();

            $table->string('reference')->unique();

            $table->string('destination_country');

            $table->string('destination_currency');

            $table->decimal('send_amount',15,2);

            $table->decimal('receive_amount',15,2);

            $table->decimal('exchange_rate',15,6);

            $table->decimal('fee',15,2)->default(0);

            $table->decimal('total',15,2);

            $table->string('status')->default('pending');

            $table->text('remark')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
