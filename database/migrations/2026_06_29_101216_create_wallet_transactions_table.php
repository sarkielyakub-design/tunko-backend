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
        Schema::create('wallet_transactions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reference')->unique();

            $table->string('gateway_reference')->nullable();

            $table->enum('type', [
                'deposit',
                'withdraw',
                'transfer',
                'airtime',
                'data',
                'bill'
            ]);

            $table->decimal('amount', 18, 2);

            $table->decimal('fee', 18, 2)
                ->default(0);

            $table->decimal('total', 18, 2);

            $table->string('currency', 10)
                ->default('NGN');

            $table->string('payment_gateway')
                ->default('paystack');

            $table->enum('status', [
                'pending',
                'processing',
                'success',
                'failed',
                'cancelled'
            ])->default('pending');

            $table->text('description')
                ->nullable();

            $table->json('meta')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'wallet_transactions'
        );
    }
};