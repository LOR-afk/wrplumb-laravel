<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->timestamp('ready_for_quotation_at')->nullable();
            $table->foreignId('forwarded_to_hr_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('quotation_handoff_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropForeign(['forwarded_to_hr_by']);

            $table->dropColumn([
                'ready_for_quotation_at',
                'forwarded_to_hr_by',
                'quotation_handoff_notes',
            ]);
        });
    }
};