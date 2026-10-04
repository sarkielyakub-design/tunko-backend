<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('kycs', 'document_country')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->string('document_country')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'level')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->unsignedTinyInteger('level')->default(1);
            });
        }

        if (!Schema::hasColumn('kycs', 'is_verified')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->boolean('is_verified')->default(false);
            });
        }

        if (!Schema::hasColumn('kycs', 'verification_provider')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->string('verification_provider')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'provider_reference')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->string('provider_reference')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'reviewed_by')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->foreignId('reviewed_by')
                    ->nullable()
                    ->constrained('admins')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('kycs', 'admin_note')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->text('admin_note')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'reject_code')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->string('reject_code')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'reviewed_at')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->timestamp('reviewed_at')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'approved_at')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable();
            });
        }

        if (!Schema::hasColumn('kycs', 'rejected_at')) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->timestamp('rejected_at')->nullable();
            });
        }

        if (
            Schema::hasColumn('kycs', 'document_front') &&
            !Schema::hasColumn('kycs', 'id_front')
        ) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->renameColumn('document_front', 'id_front');
            });
        }

        if (
            Schema::hasColumn('kycs', 'document_back') &&
            !Schema::hasColumn('kycs', 'id_back')
        ) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->renameColumn('document_back', 'id_back');
            });
        }

        if (
            Schema::hasColumn('kycs', 'selfie_image') &&
            !Schema::hasColumn('kycs', 'selfie')
        ) {
            Schema::table('kycs', function (Blueprint $table) {
                $table->renameColumn('selfie_image', 'selfie');
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty.
    }
};