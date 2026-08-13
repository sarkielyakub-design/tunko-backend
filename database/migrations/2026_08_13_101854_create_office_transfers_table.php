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
        Schema::create('office_transfers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Transfer Reference
            |--------------------------------------------------------------------------
            */

            $table->string('reference')
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | Sender
            |--------------------------------------------------------------------------
            */

            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Source Office
            |--------------------------------------------------------------------------
            |
            | Office where the transfer originates.
            |
            */

            $table->foreignId('source_office_id')
                ->nullable()
                ->constrained('offices')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Destination Office
            |--------------------------------------------------------------------------
            |
            | Office/city where recipient will collect the money.
            |
            */

            $table->foreignId('destination_office_id')
                ->nullable()
                ->constrained('offices')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Recipient Information
            |--------------------------------------------------------------------------
            */

            $table->string('recipient_phone');

            $table->string('recipient_first_name');

            $table->string('recipient_last_name');

            /*
            |--------------------------------------------------------------------------
            | Transfer Details
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 18, 2);

            $table->decimal('fee', 18, 2)
                ->default(0);

            $table->decimal('total', 18, 2);

            $table->string('currency', 10);

            /*
            |--------------------------------------------------------------------------
            | Fees Included
            |--------------------------------------------------------------------------
            |
            | true:
            |   amount already contains the fee.
            |
            | false:
            |   fee is added to the amount.
            |
            */

            $table->boolean('fees_included')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Reason
            |--------------------------------------------------------------------------
            */

            $table->string('reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->string('status')
                ->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Collection
            |--------------------------------------------------------------------------
            */

            $table->timestamp('completed_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Additional Information
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();

            $table->json('meta')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index('sender_id');

            $table->index('recipient_phone');

            $table->index('source_office_id');

            $table->index('destination_office_id');

            $table->index('status');

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_transfers');
    }
};