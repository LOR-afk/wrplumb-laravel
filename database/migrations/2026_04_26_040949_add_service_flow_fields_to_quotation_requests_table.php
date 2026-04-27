<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->string('service_flow')->default('inspection_required')->after('service_type');
            $table->string('visit_purpose')->default('inspection')->after('service_flow');
            $table->string('flow_source')->default('system')->after('visit_purpose');
            $table->text('flow_override_reason')->nullable()->after('flow_source');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_requests', function (Blueprint $table) {
            $table->dropColumn([
                'service_flow',
                'visit_purpose',
                'flow_source',
                'flow_override_reason',
            ]);
        });
    }
};