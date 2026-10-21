<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('appointment_duration_minutes')
                ->nullable()
                ->after('appointment_time');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn('appointment_duration_minutes');
        });
    }
};