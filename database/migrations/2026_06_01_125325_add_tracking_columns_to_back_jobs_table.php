<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('back_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('back_jobs', 'scheduled_by')) {
                $table->foreignId('scheduled_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('back_jobs', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('scheduled_by');
            }

            if (!Schema::hasColumn('back_jobs', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('started_at');
            }

            if (!Schema::hasColumn('back_jobs', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('resolved_at');
            }

            if (!Schema::hasColumn('back_jobs', 'resolution_notes')) {
                $table->text('resolution_notes')->nullable()->after('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('back_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('back_jobs', 'scheduled_by')) {
                $table->dropConstrainedForeignId('scheduled_by');
            }

            if (Schema::hasColumn('back_jobs', 'started_at')) {
                $table->dropColumn('started_at');
            }

            if (Schema::hasColumn('back_jobs', 'resolved_at')) {
                $table->dropColumn('resolved_at');
            }

            if (Schema::hasColumn('back_jobs', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }

            if (Schema::hasColumn('back_jobs', 'resolution_notes')) {
                $table->dropColumn('resolution_notes');
            }
        });
    }
};