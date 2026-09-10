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
        Schema::create('vouchers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Voucher Identity
            |--------------------------------------------------------------------------
            */

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | Voucher Type
            |--------------------------------------------------------------------------
            |
            | airtime
            | data
            |
            */

            $table->string('type');

            /*
            |--------------------------------------------------------------------------
            | Country / Network
            |--------------------------------------------------------------------------
            */

            $table->string('country_code', 5);

            $table->foreignId('network_id')
                ->nullable()
                ->constrained('networks')
                ->nullOnDelete();

            $table->string('network_name')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $table->string('product_name')->nullable();

            $table->decimal('amount', 15, 2);

            $table->string('currency', 10);

            /*
            |--------------------------------------------------------------------------
            | Voucher PIN
            |--------------------------------------------------------------------------
            |
            | The actual card PIN/code supplied by the company.
            |
            */

            $table->text('pin');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            |
            | available
            | reserved
            | sold
            | cancelled
            |
            */

            $table->string('status')
                ->default('available')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Purchase Reference
            |--------------------------------------------------------------------------
            */

            $table->string('purchase_reference')
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Provider / Company
            |--------------------------------------------------------------------------
            */

            $table->string('provider')->nullable();

            $table->string('provider_reference')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('sold_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional Information
            |--------------------------------------------------------------------------
            */

            $table->text('remark')
                ->nullable();

            $table->json('meta')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'type',
                'country_code',
                'status',
            ]);

            $table->index([
                'network_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
