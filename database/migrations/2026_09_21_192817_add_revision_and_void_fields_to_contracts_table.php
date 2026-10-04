<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedInteger('revision_no')
                ->default(1)
                ->after('status');

            $table->text('void_reason')
                ->nullable()
                ->after('finalized_at');

            $table->timestamp('voided_at')
                ->nullable()
                ->after('void_reason');

            $table->foreignId('voided_by')
                ->nullable()
                ->after('voided_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');

            $table->dropColumn([
                'revision_no',
                'void_reason',
                'voided_at',
            ]);
        });
    }
};