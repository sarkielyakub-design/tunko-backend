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

            // User
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Wallet
            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            // Transaction Reference
            $table->string('reference')->unique();

            // Gateway Reference
            $table->string('gateway_reference')->nullable();

            // Transaction Type
            $table->enum('type', [
                'deposit',
                'withdraw',
                'transfer',
                'airtime',
                'data',
                'bill',
            ]);

            // Amount
            $table->decimal('amount', 18, 2);

            // Fee
            $table->decimal('fee', 18, 2)
                ->default(0);

            // Total Amount
            $table->decimal('total', 18, 2);

            // Currency
            $table->string('currency', 10)
                ->default('NGN');

            // Payment Gateway
            $table->string('payment_gateway')
                ->default('paystack');

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'success',
                'failed',
                'cancelled',
            ])->default('pending');

            // Description
            $table->text('description')
                ->nullable();

            // Extra Metadata
            $table->json('meta')
                ->nullable();

            // Completion Time
            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();

            // Useful indexes
            $table->index('reference');
            $table->index('user_id');
            $table->index('wallet_id');
            $table->index('status');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};