<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->text('inspector_notes')->nullable()->after('admin_notes');
            $table->timestamp('inspected_at')->nullable()->after('inspector_notes');
            $table->timestamp('completed_at')->nullable()->after('inspected_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'inspector_notes',
                'inspected_at',
                'completed_at',
            ]);
        });
    }
};