<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('wallet_transactions', 'wallet_id')) {

            Schema::table('wallet_transactions', function (Blueprint $table) {

                $table->foreignId('wallet_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('wallets')
                    ->nullOnDelete();

            });

        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('wallet_transactions', 'wallet_id')) {

            Schema::table('wallet_transactions', function (Blueprint $table) {

                $table->dropForeign(['wallet_id']);
                $table->dropColumn('wallet_id');

            });

        }
    }
};