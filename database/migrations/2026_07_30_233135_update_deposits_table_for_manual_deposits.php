<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {

            $table->foreignId('wallet_id')
                ->nullable()
                ->after('user_id')
                ->constrained()
                ->nullOnDelete();

            $table->decimal('fee', 18, 2)
                ->default(0)
                ->after('amount');

            $table->decimal('total', 18, 2)
                ->default(0)
                ->after('fee');

            $table->string('currency')
                ->default('USD')
                ->after('total');

            $table->string('gateway')
                ->nullable()
                ->after('currency');

            $table->string('gateway_reference')
                ->nullable()
                ->after('gateway');

            $table->string('provider_status')
                ->nullable()
                ->after('gateway_reference');

            $table->longText('provider_response')
                ->nullable()
                ->after('provider_status');

            $table->foreignId('approved_by')
                ->nullable()
                ->after('status')
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->string('reject_reason')->nullable();
            $table->string('reject_code')->nullable();

            $table->string('cancel_reason')->nullable();
            $table->string('cancel_code')->nullable();

            $table->text('admin_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {

            $table->dropConstrainedForeignId('wallet_id');
            $table->dropConstrainedForeignId('approved_by');

            $table->dropColumn([
                'fee',
                'total',
                'currency',
                'gateway',
                'gateway_reference',
                'provider_status',
                'provider_response',
                'approved_at',
                'completed_at',
                'cancelled_at',
                'reject_reason',
                'reject_code',
                'cancel_reason',
                'cancel_code',
                'admin_note',
            ]);
        });
    }
};