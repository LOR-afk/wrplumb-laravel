<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('back_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('backjob_no')->unique();

            $table->foreignId('warranty_claim_id')
                ->nullable()
                ->constrained('warranty_claims')
                ->nullOnDelete();

            $table->foreignId('original_job_order_id')
                ->constrained('job_orders')
                ->cascadeOnDelete();

            $table->foreignId('quotation_request_id')
                ->nullable()
                ->constrained('quotation_requests')
                ->nullOnDelete();

            $table->foreignId('worker_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('reason')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();

            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['original_job_order_id', 'status']);
            $table->index(['warranty_claim_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('back_jobs');
    }
};
