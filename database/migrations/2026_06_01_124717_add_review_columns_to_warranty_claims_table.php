<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('warranty_claims', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('warranty_expires_at');
            }

            if (!Schema::hasColumn('warranty_claims', 'reviewed_by')) {
                $table->foreignId('reviewed_by')
                    ->nullable()
                    ->after('admin_notes')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('warranty_claims', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }

            if (!Schema::hasColumn('warranty_claims', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            }

            if (!Schema::hasColumn('warranty_claims', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (!Schema::hasColumn('warranty_claims', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_claims', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }

            if (Schema::hasColumn('warranty_claims', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }

            if (Schema::hasColumn('warranty_claims', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }

            if (Schema::hasColumn('warranty_claims', 'approved_at')) {
                $table->dropColumn('approved_at');
            }

            if (Schema::hasColumn('warranty_claims', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }

            if (Schema::hasColumn('warranty_claims', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};