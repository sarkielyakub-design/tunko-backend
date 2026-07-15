<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reference')
                ->unique();

            $table->enum('type', [
                'deposit',
                'withdrawal',
                'transfer',
                'airtime',
                'data',
                'bill_payment',
            ]);

            $table->decimal('amount', 20, 2);

            $table->decimal('fee', 20, 2)
                ->default(0);

            $table->decimal('total', 20, 2);

            $table->enum('status', [
                'pending',
                'completed',
                'failed',
            ])->default('pending');

            $table->text('description')
                ->nullable();

            $table->json('meta')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};