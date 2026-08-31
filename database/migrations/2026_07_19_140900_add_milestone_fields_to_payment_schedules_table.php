<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->string('milestone_status')
                ->default('not_ready')
                ->after('status');

            $table->timestamp('ready_for_billing_at')
                ->nullable()
                ->after('milestone_status');

            $table->foreignId('ready_for_billing_by')
                ->nullable()
                ->after('ready_for_billing_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('milestone_notes')
                ->nullable()
                ->after('ready_for_billing_by');
        });
    }

    public function down(): void
    {
        Schema::table('payment_schedules', function (Blueprint $table) {
            $table->dropForeign(['ready_for_billing_by']);

            $table->dropColumn([
                'milestone_status',
                'ready_for_billing_at',
                'ready_for_billing_by',
                'milestone_notes',
            ]);
        });
    }
};