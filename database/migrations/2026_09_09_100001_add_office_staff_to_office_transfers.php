<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_transfers', function (Blueprint $table) {
            $table->foreignId('processed_by_staff_id')
                ->nullable()
                ->after('status')
                ->constrained('office_staff')
                ->nullOnDelete();

            $table->timestamp('processing_started_at')->nullable()->after('processed_by_staff_id');
            $table->index('processed_by_staff_id');
        });
    }

    public function down(): void
    {
        Schema::table('office_transfers', function (Blueprint $table) {
            $table->dropForeign(['processed_by_staff_id']);
            $table->dropIndex(['processed_by_staff_id']);
            $table->dropColumn(['processed_by_staff_id', 'processing_started_at']);
        });
    }
};
