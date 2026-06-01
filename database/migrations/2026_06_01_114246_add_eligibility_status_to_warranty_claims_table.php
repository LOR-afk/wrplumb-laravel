<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (!Schema::hasColumn('warranty_claims', 'eligibility_status')) {
                $table->string('eligibility_status')->default('eligible')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('warranty_claims', function (Blueprint $table) {
            if (Schema::hasColumn('warranty_claims', 'eligibility_status')) {
                $table->dropColumn('eligibility_status');
            }
        });
    }
};