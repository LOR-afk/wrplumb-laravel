<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('submitted_by')
                ->nullable()
                ->after('payment_schedule_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('proof_path')
                ->nullable()
                ->after('reference_number');

            $table->foreignId('verified_by')
                ->nullable()
                ->after('received_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')
                ->nullable()
                ->after('verified_by');

            $table->text('verification_notes')
                ->nullable()
                ->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_by');
            $table->dropColumn('proof_path');
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn('verified_at');
            $table->dropColumn('verification_notes');
        });
    }
};