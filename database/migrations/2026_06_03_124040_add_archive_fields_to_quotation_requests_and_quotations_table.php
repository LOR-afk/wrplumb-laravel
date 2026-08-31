<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('quotation_requests', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('completed_at');
            }

            if (!Schema::hasColumn('quotation_requests', 'archive_reason')) {
                $table->string('archive_reason')->nullable()->after('archived_at');
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
            }

            if (!Schema::hasColumn('quotations', 'archive_reason')) {
                $table->string('archive_reason')->nullable()->after('archived_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            if (Schema::hasColumn('quotation_requests', 'archived_at')) {
                $table->dropColumn('archived_at');
            }

            if (Schema::hasColumn('quotation_requests', 'archive_reason')) {
                $table->dropColumn('archive_reason');
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'archived_at')) {
                $table->dropColumn('archived_at');
            }

            if (Schema::hasColumn('quotations', 'archive_reason')) {
                $table->dropColumn('archive_reason');
            }
        });
    }
};