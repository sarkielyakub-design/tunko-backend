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
        if (!Schema::hasColumn('kycs', 'id_type')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->string('id_type')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'id_number')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->string('id_number')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty because these columns
        // may have existed before this migration.
    }
};