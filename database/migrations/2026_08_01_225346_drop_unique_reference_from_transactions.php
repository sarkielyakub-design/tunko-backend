<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropUnique('transactions_reference_unique');
        $table->index('reference');
    });
}

public function down()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropIndex(['reference']);
        $table->unique('reference');
    });
}
};
