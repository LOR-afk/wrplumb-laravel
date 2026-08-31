<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_request_id')
                ->constrained('quotation_requests')
                ->cascadeOnDelete();

            $table->foreignId('inspection_report_id')
                ->nullable()
                ->constrained('inspection_reports')
                ->cascadeOnDelete();

            $table->foreignId('updated_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('status');
            $table->text('notes')->nullable();

            $table->timestamp('recorded_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_status_logs');
    }
};