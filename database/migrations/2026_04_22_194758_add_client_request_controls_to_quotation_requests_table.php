<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->string('client_action_request')->nullable()->after('cancel_reason'); // reschedule, cancel
            $table->string('client_action_status')->nullable()->after('client_action_request'); // pending, approved, declined
            $table->date('client_requested_date')->nullable()->after('client_action_status');
            $table->time('client_requested_time')->nullable()->after('client_requested_date');
            $table->text('client_request_reason')->nullable()->after('client_requested_time');
            $table->timestamp('client_requested_at')->nullable()->after('client_request_reason');
            $table->timestamp('client_request_reviewed_at')->nullable()->after('client_requested_at');
            $table->text('client_request_review_notes')->nullable()->after('client_request_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'client_action_request',
                'client_action_status',
                'client_requested_date',
                'client_requested_time',
                'client_request_reason',
                'client_requested_at',
                'client_request_reviewed_at',
                'client_request_review_notes',
            ]);
        });
    }
};