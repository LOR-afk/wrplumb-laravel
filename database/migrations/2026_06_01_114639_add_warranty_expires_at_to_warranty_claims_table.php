<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('warranty_claims', 'warranty_expires_at')) {
                $table->timestamp('warranty_expires_at')->nullable()->after('eligibility_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_claims', 'warranty_expires_at')) {
                $table->dropColumn('warranty_expires_at');
            }
        });
    }
};