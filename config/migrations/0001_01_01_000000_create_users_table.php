<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
    $table->id();

    $table->string('first_name');
    $table->string('last_name');

    $table->string('email')->unique();
    $table->string('phone')->unique();

    $table->string('country')->nullable();

    $table->string('referral_code')->nullable();

    $table->boolean('is_verified')->default(false);
    $table->boolean('is_active')->default(true);

    $table->timestamp('email_verified_at')->nullable();

    $table->string('password');
    $table->rememberToken();

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'country',
                'referral_code',
                'is_verified',
                'is_active',
            ]);

        });
    }
};