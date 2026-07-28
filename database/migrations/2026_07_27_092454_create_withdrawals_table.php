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
        Schema::create('withdrawals', function (Blueprint $table) {

            $table->id();

            // Relationships
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('wallet_id')
                ->constrained()
                ->cascadeOnDelete();

            // Reference
            $table->string('reference')->unique();

            // Amounts
            $table->decimal('amount', 18, 2);
            $table->decimal('fee', 18, 2)->default(0);
            $table->decimal('total', 18, 2);

            // Currency
            $table->string('currency', 10);

            // Provider
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->string('provider_status')->nullable();
            $table->text('provider_response')->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'rejected',
                'cancelled',
            ])->default('pending');

            // Reject
            $table->text('reject_reason')->nullable();
            $table->string('reject_code')->nullable();

            // Cancel
            $table->text('cancel_reason')->nullable();
            $table->string('cancel_code')->nullable();

            // Approval
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Retry
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();

            // Notes
            $table->text('admin_note')->nullable();
            $table->text('remark')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('reference');
            $table->index('provider');
            $table->index('currency');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};