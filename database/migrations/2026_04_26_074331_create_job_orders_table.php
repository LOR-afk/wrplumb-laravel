<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_request_id')
                ->constrained('quotation_requests')
                ->cascadeOnDelete();

            $table->foreignId('worker_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('job_order_no')->unique();

            $table->string('service_flow')->nullable();
            $table->string('service_type')->nullable();
            $table->string('project_type')->nullable();

            $table->date('scheduled_date')->nullable();
            $table->string('scheduled_time')->nullable();

            $table->string('status')->default('pending');
            // pending, scheduled, in_progress, completed, cancelled

            $table->text('scope_of_work')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('work_remarks')->nullable();
            $table->text('completion_notes')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_orders');
    }
};