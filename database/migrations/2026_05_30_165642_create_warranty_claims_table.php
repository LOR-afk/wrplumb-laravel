<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_no')->unique();

            $table->foreignId('job_order_id')
                ->constrained('job_orders')
                ->cascadeOnDelete();

            $table->foreignId('quotation_request_id')
                ->nullable()
                ->constrained('quotation_requests')
                ->nullOnDelete();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('claim_type')->default('service_issue');
            $table->text('issue_description');
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time')->nullable();

            $table->string('status')->default('submitted');
            $table->unsignedInteger('warranty_days')->default(30);
            $table->date('eligible_until')->nullable();

            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['job_order_id', 'status']);
            $table->index(['client_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
    }
};
