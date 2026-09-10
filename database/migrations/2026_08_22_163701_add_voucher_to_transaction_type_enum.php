<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not require changing the enum definition.
        // Laravel/SQLite stores enum values as TEXT.
    }

    public function down(): void
    {
        // Nothing to rollback for SQLite.
    }
};