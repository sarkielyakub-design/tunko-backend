<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PostgreSQL
        |--------------------------------------------------------------------------
        |
        | Railway uses PostgreSQL, so update the CHECK constraint there.
        |
        */

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE transactions
                DROP CONSTRAINT IF EXISTS transactions_type_check
            ');

            DB::statement("
                ALTER TABLE transactions
                ADD CONSTRAINT transactions_type_check
                CHECK (
                    type IN (
                        'deposit',
                        'transfer',
                        'withdrawal',
                        'voucher'
                    )
                )
            ");
        }

        /*
        |--------------------------------------------------------------------------
        | SQLite
        |--------------------------------------------------------------------------
        |
        | SQLite does not support dropping a CHECK constraint directly.
        | Nothing is required locally because SQLite is only being used
        | for local development/testing.
        |
        */

        if (DB::getDriverName() === 'sqlite') {
            return;
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE transactions
                DROP CONSTRAINT IF EXISTS transactions_type_check
            ');

            DB::statement("
                ALTER TABLE transactions
                ADD CONSTRAINT transactions_type_check
                CHECK (
                    type IN (
                        'deposit',
                        'transfer',
                        'withdrawal'
                    )
                )
            ");
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }
    }
};