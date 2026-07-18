<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transfers', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('recipient_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('sender_wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            $table->foreignId('recipient_wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            $table->decimal('amount', 18, 2);

            $table->decimal('fee', 18, 2)->default(0);

            $table->decimal('total', 18, 2);

            $table->string('currency', 10);

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'reversed'
            ])->default('pending');

            $table->text('description')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('reference');
            $table->index('sender_id');
            $table->index('recipient_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transfers');
    }
};