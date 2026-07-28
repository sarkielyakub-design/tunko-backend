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

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            $table->string('first_name');

            $table->string('last_name');

            $table->string('middle_name')->nullable();

            $table->date('date_of_birth')->nullable();

            $table->string('gender')->nullable();

            $table->string('marital_status')->nullable();

            $table->string('nationality')->nullable();

            $table->string('occupation')->nullable();

            $table->string('source_of_income')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            $table->string('country')->nullable();

            $table->string('state')->nullable();

            $table->string('city')->nullable();

            $table->text('address')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('id_type')->nullable();

            $table->string('id_number')->nullable();

            $table->string('document_type')->nullable();

            $table->string('document_country')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Uploaded Documents
            |--------------------------------------------------------------------------
            */

            $table->string('id_front')->nullable();

            $table->string('id_back')->nullable();

            $table->string('selfie')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('level')
                ->default(1);

            $table->boolean('is_verified')
                ->default(false);

            $table->string('verification_provider')
                ->nullable();

            $table->string('provider_reference')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'expired',
            ])->default('pending');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->text('admin_note')->nullable();

            $table->text('rejection_reason')->nullable();

            $table->string('reject_code')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('reviewed_at')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kycs');
    }
};