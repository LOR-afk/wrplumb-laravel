<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->string('job_type', 20)->nullable()->after('job_order_no');
        });

        DB::table('job_orders')
            ->where('service_flow', 'inspection_required')
            ->update(['job_type' => 'inspection']);

        DB::table('job_orders')
            ->where(function ($query) {
                $query->whereNull('job_type')
                    ->orWhere('job_type', '');
            })
            ->update(['job_type' => 'service']);

        Schema::table('job_orders', function (Blueprint $table) {
            $table->unique(
                ['quotation_request_id', 'job_type'],
                'job_orders_request_type_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropUnique('job_orders_request_type_unique');
            $table->dropColumn('job_type');
        });
    }
};
