<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kycs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();

            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();

            $table->string('nationality')->nullable();
            $table->string('occupation')->nullable();
            $table->string('source_of_income')->nullable();

            // Address
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            // Identity
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();

            $table->string('document_front')->nullable();
            $table->string('document_back')->nullable();
            $table->string('selfie_image')->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kycs');
    }
};