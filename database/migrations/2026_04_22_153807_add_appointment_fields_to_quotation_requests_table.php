<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->time('preferred_time')->nullable()->after('preferred_date');
            $table->date('appointment_date')->nullable()->after('preferred_time');
            $table->time('appointment_time')->nullable()->after('appointment_date');
            $table->string('appointment_status')->default('pending')->after('status');
            $table->timestamp('approved_at')->nullable()->after('appointment_status');
            $table->timestamp('rescheduled_at')->nullable()->after('approved_at');
            $table->timestamp('cancelled_at')->nullable()->after('rescheduled_at');
            $table->text('cancel_reason')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'preferred_time',
                'appointment_date',
                'appointment_time',
                'appointment_status',
                'approved_at',
                'rescheduled_at',
                'cancelled_at',
                'cancel_reason',
            ]);
        });
    }
};