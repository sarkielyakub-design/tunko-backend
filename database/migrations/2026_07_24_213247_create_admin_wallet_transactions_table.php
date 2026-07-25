<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_wallet_transactions', function (Blueprint $table) {

            $table->id();

            $table->foreignId('admin_wallet_id')
                ->constrained('admin_wallets')
                ->cascadeOnDelete();

            $table->string('reference')->unique();

            $table->enum('type', [
                'credit',
                'debit',
            ]);

            $table->decimal('amount', 20, 2);

            $table->decimal('balance_before', 20, 2);

            $table->decimal('balance_after', 20, 2);

            $table->string('currency', 10)->default('XAF');

            $table->string('source')->nullable();

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_wallet_transactions');
    }
};