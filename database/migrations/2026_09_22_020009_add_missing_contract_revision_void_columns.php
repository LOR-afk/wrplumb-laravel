<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('contracts', 'revision_no')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->unsignedInteger('revision_no')
                    ->default(1)
                    ->after('status');
            });
        }

        if (!Schema::hasColumn('contracts', 'void_reason')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->text('void_reason')
                    ->nullable()
                    ->after('finalized_at');
            });
        }

        if (!Schema::hasColumn('contracts', 'voided_at')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->timestamp('voided_at')
                    ->nullable()
                    ->after('void_reason');
            });
        }

        if (!Schema::hasColumn('contracts', 'voided_by')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->foreignId('voided_by')
                    ->nullable()
                    ->after('voided_at')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contracts', 'voided_by')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('voided_by');
            });
        }

        if (Schema::hasColumn('contracts', 'voided_at')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn('voided_at');
            });
        }

        if (Schema::hasColumn('contracts', 'void_reason')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn('void_reason');
            });
        }

        if (Schema::hasColumn('contracts', 'revision_no')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->dropColumn('revision_no');
            });
        }
    }
};