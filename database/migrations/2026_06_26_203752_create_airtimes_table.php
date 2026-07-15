<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airtimes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('reference')->unique();

            $table->foreignId('country_id');

            $table->string('country');

            $table->string('network');

            $table->string('phone');

            $table->decimal('amount', 15, 2);

            $table->string('currency');

            $table->string('provider')
                ->nullable();

            $table->string('provider_reference')
                ->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('pending');

            $table->text('remark')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airtimes');
    }
};