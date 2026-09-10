<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_wallets', function (Blueprint $table) {

            $table->id();

            $table->string('wallet_name');
            $table->string('wallet_number')->unique();

            $table->string('currency', 10)->default('XAF');

            $table->decimal('balance', 20, 2)->default(0);

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_wallets');
    }
};